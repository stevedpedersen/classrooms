<?php

/**
 */
class Classrooms_Room_AdminController extends At_Admin_Controller
{
    public static $AllRoomAvEquipment = [
        'lcd_proj'=>'LCD Projector', 'lcd_tv'=>'LCD TV', 'vcr_dvd'=>'VCR/DVD', 'hdmi'=>'HDMI', 'vga'=>'VGA',
        'mic'=>'Mic', 'coursestream'=>'CourseStream', 'doc_cam'=>'Doc Cam', 'zoom'=>'Zoom Enabled'
    ];

    public static function getRouteMap ()
    {
        return [
            '/admin/rooms/defaults' => ['callback' => 'setDefaults'],
            '/admin/rooms/import' => ['callback' => 'importRoomData'],
            '/admin/rooms/unconfigured' => ['callback' => 'unconfiguredReport'],
        ];
    }

    public function setDefaults ()
    {
        $siteSettings = $this->getApplication()->siteSettings;
        
        if ($this->getPostCommand() == 'save' && $this->request->wasPostedByUser())
        {           
            if ($defaultRoomDescription = $this->request->getPostParameter('default-room-description'))
            {
                $siteSettings->setProperty('default-room-description', $defaultRoomDescription);
                $this->flash('The default room description has been saved.');
                $this->response->redirect('admin/rooms/defaults');
            }
        }
        
        if ($defaultRoomDescription = $siteSettings->getProperty('default-room-description'))
        {
            $this->template->defaultRoomDescription = $defaultRoomDescription;
        }
    }

    public function unconfiguredReport ()
    {
        if ($this->request->wasPostedByUser())
        {
            $roomSchema = $this->schema('Classrooms_Room_Location');
            $buildingSchema = $this->schema('Classrooms_Room_Building');

            header("Content-Type: application/download\n");
            header('Content-Disposition: attachment; filename="'.(new DateTime)->format('Y-m-d').'-results.csv"' . "\n");
            $handle = fopen('php://output', 'w+');

            $results = $roomSchema->find(
                $roomSchema->configured->isFalse()->orIf($roomSchema->configured->isNull()),
                ['orderBy' => ['building_id', 'number']]
            );

            if ($handle)
            {
                $headers = array(
                    'Building',
                    'Room#', 
                );
                fputcsv($handle, $headers);

                foreach ($results as $result)
                {
                    $row = array(
                        $result->building->name,
                        $result->number,
                    );
                    fputcsv($handle, $row);
                }
            }
            
            exit;
        }
    }

    public function importRoomData ()
    {
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'upload':
                    if ($file = $this->request->getFileUpload('csv'))
                    {
                        if ($file->isValid() && ($pathInfo = pathinfo($file->getRemoteName())))
                        {
                            if ($pathInfo['extension'] === 'csv')
                            {

                                if ($handle = fopen($file->getLocalPath(), "r"))
                                {
                                    if ($headers = fgetcsv($handle))
                                    {
                                        $rowLength = count($headers);

                                        $viewer = $this->getAccount();

                                        $siteSettings = $this->getApplication()->siteSettings;
                                        $locationSchema = $this->schema('Classrooms_Room_Location');
                                        $buildingSchema = $this->schema('Classrooms_Room_Building');
                                        $roomTypeSchema = $this->schema('Classrooms_Room_Type');

                                        $allRoomTypes = $roomTypeSchema->findValues(['name' => 'id']);
                                        $allBuildings = $buildingSchema->getAll();
                                        
                                        $buildingRooms = array();

                                        foreach ($allBuildings as $building)
                                        {
                                            $buildingRooms[$building->name] = array(
                                                'building' => $building, 
                                                'rooms' => array()
                                            );

                                            foreach ($building->locations as $room)
                                            {
                                                $buildingRooms[$building->name]['rooms'][$room->number] = $room;
                                            }
                                        }

                                        $allScheduledBy = unserialize($siteSettings->getProperty('scheduled-by'));
                                        $allSupportedBy = unserialize($siteSettings->getProperty('supported-by'));

                                        $tx = $this->getDataSource()->createTransaction();

                                        try
                                        {
                                            while (($row = fgetcsv($handle, 10000)) !== false) 
                                            {
                                                $data = array();
                                                if (count($row) < $rowLength) break;

                                                for ($i = 0; $i < $rowLength; $i++)
                                                {
                                                    $data[$headers[$i]] = $row[$i];
                                                }

                                                $newLocation = false;

                                                $building = trim($data['Building']);
                                                $room = trim($data['Room#']);
                                                $roomType = trim($data['Room Type']);
                                                $scheduledBy = trim($data['Scheduled By']);
                                                $supportedBy = trim($data['Supported By']);
                                                $capacity = trim($data['Capacity']);

                                                $avEquipment = [
                                                    'lcd_proj' => trim($data['LCD Projector']),
                                                    'lcd_tv' => trim($data['LCD TV']),
                                                    'vcr_dvd' => trim($data['VCR/DVD']),
                                                    'blu_ray' => trim($data['Blu-ray']),
                                                    'hdmi' => trim($data['HDMI']),
                                                    'vga' => trim($data['VGA']),
                                                    'mic' => trim($data['Mic']),
                                                    'coursestream' => trim($data['CourseStream']),
                                                    'doc_cam' => trim($data['Doc Cam']),
                                                    'zoom' => trim($data['Zoom Enabled']),
                                                ];
                                                foreach ($avEquipment as $key => $equip)
                                                {
                                                    if ($equip != '1')
                                                    {
                                                        unset($avEquipment[$key]);
                                                    }
                                                }

                                                //$avEquipment = array_filter($avEquipment);

                                                if ($roomType)
                                                {
                                                    if (!isset($allRoomTypes[$roomType]))
                                                    {
                                                        $newType = $roomTypeSchema->createInstance();
                                                        $newType->name = $roomType;
                                                        $newType->deleted = false;
                                                        $newType->save($tx);
                                                        $allRoomTypes[$roomType] = $newType->id;
                                                    }
                                                }

                                                if ($building)
                                                {
                                                    if ($building === 'HSS Building')
                                                    {
                                                        $building = 'Health & Social Services';
                                                    }
                                                    if ($building === 'Gym')
                                                    {
                                                        $building = 'Gymnasium';
                                                    }

                                                    if (!isset($buildingRooms[$building]))
                                                    {
                                                        $newBuilding = $buildingSchema->createInstance();
                                                        $newBuilding->name = $building;
                                                        $newBuilding->deleted = false;
                                                        $newBuilding->save($tx);
                                                        $buildingRooms[$building] = array(
                                                            'building' => $newBuilding,
                                                            'rooms' => array()
                                                        );
                                                    }

                                                    $building = $buildingRooms[$building]['building'];
                                                    
                                                    if ($room)
                                                    {
                                                        if (!isset($buildingRooms[$building->name]['rooms'][$room]))
                                                        {
                                                            $newLocation = true;
                                                            $newRoom = $locationSchema->createInstance();
                                                            $newRoom->applyDefaults($room, $building);
                                                            $newRoom->save($tx);
                                                            $newRoom->addNote('New room created', $viewer);
                                                            $buildingRooms[$building->name]['rooms'][$room] = $newRoom;
                                                        }

                                                        if ($scheduledBy !== '' && !isset($allScheduledBy[$scheduledBy]))
                                                        {
                                                            $allScheduledBy[$scheduledBy] = $scheduledBy;
                                                        }
                                                        if ($supportedBy !== '' && !isset($allSupportedBy[$supportedBy]))
                                                        {
                                                            $allSupportedBy[$supportedBy] = $supportedBy;
                                                        } 
                                                        elseif ($supportedBy === '')
                                                        {
                                                            $supportedBy = 'Academic Technology';
                                                        }
                                                        
                                                        $room = $buildingRooms[$building->name]['rooms'][$room];
                                                        if ($room->description === 'This room has not been configured.')
                                                        {
                                                            $room->description = '';
                                                        }
                                                        $room->building = $building;
                                                        $room->type_id = $allRoomTypes[$roomType];
                                                        $room->supportedBy = $supportedBy;
                                                        $room->scheduledBy = $scheduledBy;
                                                        $room->capacity = $capacity;
                                                        $room->avEquipment = serialize($avEquipment);
                                                        $room->configured = true;
                                                        $room->save();

                                                        $internal = $this->schema('Classrooms_Room_InternalNote')->createInstance();
                                                        $internal->message = $message = $newLocation ?
                                                            'Added new location through import' :
                                                            'Updated location through import';
                                                        $internal->addedBy = $viewer;
                                                        $internal->createdDate = new DateTime;
                                                        $internal->location = $room;
                                                        $internal->save();
                                                        $internal->addNote("New internal note added: $message", $viewer);
                                                    }
                                                }
                                            }

                                            $siteSettings->setProperty('scheduled-by', serialize($allScheduledBy));
                                            $siteSettings->setProperty('supported-by', serialize($allSupportedBy));

                                            $tx->commit();
                                        }
                                        catch (Exception $e)
                                        {
                                            $tx->rollback();
                                        }
                                    } 
                                } 
                            }
                        }
                    }

                    break;
            }
        }
    }
}