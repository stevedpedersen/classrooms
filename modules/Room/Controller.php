<?php

/**
 * The Rooms controller
 * 
 * @author      Steve Pedersen (pedersen@sfsu.edu)
 * @copyright   Copyright &copy; San Francisco State University
 */
class Classrooms_Room_Controller extends Classrooms_Master_Controller
{
    public static $AllRoomFacets = [
        'lcd_proj'=>'LCD Proj', 'lcd_tv'=>'LCD TV', 'vcr_dvd'=>'VCR/DVD', 'hdmi'=>'HDMI', 'vga'=>'VGA', 'data'=>'Data',
        'scr'=>'Scr', 'mic'=>'Mic', 'coursestream'=>'CourseStream', 'doc_cam'=>'Doc Cam'
    ];

    public static function getRouteMap ()
    {
        return [
            '/rooms' => ['callback' => 'listRooms'],
            '/rooms/:id' => ['callback' => 'view'],
            '/rooms/:id/edit' => ['callback' => 'editRoom', ':id' => '[0-9]+|new'],
            '/rooms/:roomid/tutorials/:id/edit' => ['callback' => 'editTutorial', ':id' => '[0-9]+|new'],
            '/rooms/:id/configurations/:cid/edit' => ['callback' => 'editConfiguration'],
            '/buildings/:id/edit' => ['callback' => 'editBuilding', ':id' => '[0-9]+|new'],
            '/types/:id/edit' => ['callback' => 'editType', ':id' => '[0-9]+|new'],
            '/rooms/:id/tutorials/upload' => ['callback' => 'uploadImages'],
            '/rooms/:id/files/:fileid/download' => ['callback' => 'downloadImage'],
            '/configurations' => ['callback' => 'listConfigurations'],
            '/configurations/:id' => ['callback' => 'viewConfigurationBundle'],
            '/configurations/:id/edit' => ['callback' => 'editConfigurationBundle'],
        ];
    }

    public function listConfigurations ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('list software');
        $this->template->canEdit = $this->hasPermission('edit room');

        $configs = $this->schema('Classrooms_Room_Configuration');
        $this->template->configurations = $configs->find(
        	$configs->isBundle->isTrue()->andIf(
        		$configs->deleted->isNull()->orIf($configs->deleted->isFalse())
            ), ['orderBy' => 'model']
        );
    }

    public function viewConfigurationBundle ()
    {
        $viewer = $this->requireLogin();
        $config = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Configuration', 'id');
        $this->addBreadcrumb('configurations', 'List Configurations');
        $this->addBreadcrumb('configurations/' . $config->id . '/edit', 'Edit');

        $this->template->config = $config;
    }

    public function editConfigurationBundle ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit room');
        $this->addBreadcrumb('configurations', 'List Configurations');

        $titles = $this->schema('Classrooms_Software_Title');
        $licenses = $this->schema('Classrooms_Software_License');
        $configs = $this->schema('Classrooms_Room_Configuration');

        $config = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Configuration', 'id', ['allowNew' => true]);

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $configData = $this->request->getPostParameters();
                
                    $config->addNote(
                        'Configuration Bundle ' . ($config->id ? 'updated' : 'created'), 
                        $viewer, 
                        $this->request->getPostParameters()
                    );
                    $config->absorbData($configData);
                    $config->isBundle = true;
                    $config->adBound = isset($configData['adBound']);
                    $config->createdDate = $config->createdDate ?? new DateTime;
                    $config->modifiedDate = new DateTime;
                    $config->save();

                    $this->saveConfigurationLicenses($config, $configData);

                    $this->flash('Configuration bundle saved');
                    break;

                case 'delete':
                    $config->deleted = true;
                    $config->save();
                    $config->addNote('Configuration deleted', $viewer);

                    $this->flash('Deleted');
                    break;
            }

            $this->response->redirect('configurations/' . $config->id);
        }

        $softwareLicenses = [];
        foreach ($licenses->getAll() as $license)
        {
            if (!isset($softwareLicenses[$license->version->title->id]))
            {
                $softwareLicenses[$license->version->title->id] = [];
            }
            $softwareLicenses[$license->version->title->id][] = $license;
        }

        $this->template->config = $config;
        $this->template->softwareLicenses = $softwareLicenses;
    }

    public function editRoom ()
    {
    	$this->addBreadcrumb('rooms', 'List Rooms');
        $viewer = $this->requireLogin();
        $this->requirePermission('edit room');

        $location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id', ['allowNew' => true]);
        $configs = $this->schema('Classrooms_Room_Configuration');
        $types = $this->schema('Classrooms_Room_Type');
        $buildings = $this->schema('Classrooms_Room_Building');
        $licenses = $this->schema('Classrooms_Software_License');
        $notes = $this->schema('Classrooms_Notes_Entry');
        
        $customConfigurations = $location->customConfigurations;
        if ($cid = $this->request->getQueryParameter('configuration', null))
        {
            $selectedConfiguration = $configs->get($cid);
        }
        else
        {
            $selectedConfiguration = !empty($customConfigurations) ? $customConfigurations[0] : $configs->createInstance();
        }
        
        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $data = $this->request->getPostParameters();
                    // echo "<pre>"; var_dump($data['config']); die;
                    
                    if (!isset($data['room']['number']) || $data['room']['number'] === '')
                    {
                        $this->flash('Room NOT saved. Please specify a room number', 'danger');
                        $this->response->redirect('rooms/new/edit');
                    }

                    $new = (bool) !$location->id;
                    $locationData = $data['room'];                                       

                    if ($location->hasDiff($locationData))
                    {
                        $location->addNote('Room details updated', $viewer, $location->getDiff($locationData));
                    }
                    $location->building_id = $locationData['building'];
                    $location->type_id = $locationData['type'];
                    $location->number = $locationData['number'];
                    $location->description = $locationData['description'];
                    $location->capacity = $locationData['capacity'];
                    $location->description = $locationData['description'];
                    $location->url = $locationData['url'];
                    $location->facets = isset($locationData['facets']) ? serialize($locationData['facets']) : '';
                    $location->createdDate = $location->createdDate ?? new DateTime;
                    $location->modifiedDate = new DateTime;
                    $location->save();
                    if ($new)
                    {
                        $location->addNote('New room created', $viewer);
                    }
                    
                    if ((isset($data['config']['new']['model']) && $data['config']['new']['model'] !== '') || 
                        isset($data['config']['existing']))
                    {
                        $new = false;
                        if (isset($data['config']['new']['model']) && $data['config']['new']['model'] !== '')
                        {
                            $configData = $data['config']['new'];
                            $config = $configs->createInstance();
                            $new = true;
                        }
                        else
                        {
                            $configData = $data['config']['existing'];
                            $config = $selectedConfiguration;
                            if ($config->hasDiff($configData))
                            {
                                $config->addNote('Custom configuration updated: '. $config->model, $viewer, $config->getDiff($configData));
                            }
                        }
                        $config->absorbData($configData);
                        $config->location = $configData['location'];
                        $config->adBound = isset($configData['adBound']);
                        $config->createdDate = $config->createdDate ?? new DateTime;
                        $config->modifiedDate = new DateTime;
                        $config->save();
                        
                        if ($new)
                        {
                            $location->configurations->add($config);
                            $location->configurations->save();
                            $config->addNote('New custom configuration created: '. $config->model, $viewer);
                        }

                        $licenseChanges = $this->saveConfigurationLicenses($config, $configData);
                    }

                    $bundleChanges = $this->saveRoomBundles($location, $this->request->getPostParameters());

                    $this->flash('Room saved.');
                    $this->response->redirect('rooms/' . $location->id);

                    break;

    			case 'delete':
                    $location->deleted = true;
                    $location->save();
                    $location->configurations->removeAll();
                    $location->addNote('Room deleted', $viewer);

                    $this->flash('Room deleted');
                    $this->response->redirect('rooms/' . $location->id);
    				break;
            }
        }

        $softwareLicenses = [];
        foreach ($licenses->getAll() as $license)
        {
            if (!isset($softwareLicenses[$license->version->title->id]))
            {
                $softwareLicenses[$license->version->title->id] = [];
            }
            $softwareLicenses[$license->version->title->id][] = $license;
        }
        
        $this->template->location = $location;
        $this->template->selectedConfiguration = $selectedConfiguration;
        $this->template->customConfigurations = $customConfigurations;
        $this->template->types = $types->getAll();
        $this->template->buildings = $buildings->getAll(['orderBy' => 'code']);
        $this->template->roomFacets = $location->facets ? unserialize($location->facets) : [];
        $this->template->allFacets = self::$AllRoomFacets;
        $this->template->softwareLicenses = $softwareLicenses;
        $this->template->bundles = $configs->find($configs->isBundle->isTrue(), ['orderBy' => 'model']);
        $this->template->notes = $notes->find($notes->path->like($location->getNotePath().'%'), ['orderBy' => '-createdDate']);
    }

    public function editTutorial () 
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit room');

    	$location = $this->requireExists($this->schema('Classrooms_Room_Location')->get($this->getRouteVariable('roomid')));
    	$tutorial = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Tutorial', 'id', ['allowNew' => true]);
    	$notes = $this->schema('Classrooms_Notes_Entry');

    	if ($this->request->wasPostedByUser())
    	{
    		switch ($this->getPostCommand())
    		{
    			case 'save':
                    $new = (bool) !$tutorial->id;
                    if (!$new && $tutorial->hasDiff($this->request->getPostParameters()))
                    {
                        $tutorial->addNote('Tutorial updated', $viewer, $tutorial->getDiff($this->request->getPostParameters()));
                    }
    				$tutorial->location_id = $location->id;
    				$tutorial->name = $this->request->getPostParameter('name');
                    $tutorial->headerImageUrl = $this->request->getPostParameter('headerImageUrl');
                    $tutorial->youtubeEmbedCode = $this->request->getPostParameter('youtubeEmbedCode');
    				$tutorial->description = $this->request->getPostParameter('description');
    				$tutorial->createdDate = $tutorial->createdDate ?? new DateTime;
    				$tutorial->modifiedDate = new DateTime;
    				$tutorial->save();
                    if ($new)
                    {
                        $tutorial->addNote('Tutorial created', $viewer);
                    }

    				$this->flash('Tutorial saved for room '. $location->codeName);
    				$this->response->redirect('rooms/' . $location->id);

    				break;

    			case 'delete':
                    $tutorial->deleted = true;
                    $tutorial->save();
                    $tutorial->addNote('Tutorial deleted', $viewer);

                    $this->flash('Tutorial deleted');
                    $this->response->redirect('rooms/' . $location->id);
    				break;
    		}
    	}

        foreach ($location->images as $image)
        {   
            $image->fullUrl = $this->baseUrl($image->imageSrc);
        }

        $this->template->images = $location->images;
    	$this->template->room = $location;
    	$this->template->tutorial = $tutorial;
    	$this->template->notes = $notes->find($notes->path->like($tutorial->getNotePath().'%'), ['orderBy' => '-createdDate']);
    }

    public function editBuilding () {}
    public function editType () {}

    public function fetchInstructorsRooms ($terms='2213')
    {
        $service = new At_ClassData_Service($this->getApplication());
        $locations = $this->schema('Classrooms_Room_Location');
        $courseSections = $this->schema('Classrooms_ClassData_CourseSection');
        $locations = $locations->find(
            $location->deleted->isNull()->orIf($locations->deleted->isFalse()),
            ['orderBy' => ['buildingId', 'number']]
        );

        $instructorsRooms = [];
        foreach ($locations as $location)
        {
            foreach (explode(',', $terms) as $term)
            {
                $instructorsRooms[$term] = [];
                $facilityId = $location->building->code . str_pad($location->number, 4, '0', STR_PAD_LEFT);
                $schedules = $service->getSchedules($term, $facilityId);
                
                foreach ($schedules['courseSchedules'] as $id => $courseSchedule)
                {
                    $courseSection = $courseSections->get($id);
                    $instructors = $courseSection->getInstructors();

                    foreach ($instructors as $instructor)
                    {
                        if (!isset($instructorsRooms[$term][$instructor->id]))
                        {
                            $instructorsRooms[$term][$instructor->id] = [];
                        }

                        foreach ($courseSchedule as $schedule)
                        {
                            $instructorsRooms[$term][$instructor->id] = [
                                'course_section_id' => $id,
                                'location_id' => $location->id,
                                'facility_id' => $courseSchedule['facility']['id'],
                                'schedule' => $schedule
                            ];
                        }                        
                    }
                }
            }
        }

        return $instructorsRooms;
    }

    public function view ()
    {
        $location = $this->helper('activeRecord')->fromRoute('Classrooms_Room_Location', 'id');
    	$this->addBreadcrumb('rooms', 'List Rooms');
        $this->addBreadcrumb('rooms/' . $location->id . '/edit', 'Edit');
        
        $notes = $this->schema('Classrooms_Notes_Entry');

        $service = new At_ClassData_Service($this->getApplication());
        
        $app = $this->getApplication();
        $importer = $app->moduleManager->getExtensionByName('at:classrooms:classdata/importer', 'importer');
        $importer->import('2213');

        echo "<pre>"; var_dump($service->getDepartments()['departments']); die;
        

        $facilityId = $location->building->code . str_pad($location->number, 4, '0', STR_PAD_LEFT);
        $facilityId = 'FA0153';
        // $facilityId = 'TH0529';
        $facilities = $service->getFacilities()['facilities'];
        // echo "<pre>"; var_dump($facilities['FA0153']); die;

        
        $schedules = $service->getSchedules('2213', $facilityId);
        $courses = $schedules['courseSchedules']['courses'];
        foreach ($courses as $course)
        {
            echo "<pre>"; var_dump($course, $schedules['courseSchedules']); die;
            
        }
        
        
        $this->template->mode = $this->request->getQueryParameter('mode', 'basic');
        $this->template->canEdit = $this->hasPermission('edit room');
    	$this->template->room = $location;
    	$this->template->allFacets = self::$AllRoomFacets;
        $this->template->notes = $notes->find($notes->path->like($location->getNotePath().'%'), ['orderBy' => '-createdDate']);
    }

    public function listRooms ()
    {
    	$viewer = $this->getAccount();
        // $this->requirePermission('list rooms');
        $this->template->canEdit = $this->hasPermission('edit room');

        $service = new At_ClassData_Service($this->getApplication());
        $facilities = $service->getFacilities();
        // echo "<pre>"; var_dump($facilities); die;
        

        $locations = $this->schema('Classrooms_Room_Location');
        $buildings = $this->schema('Classrooms_Room_Building')->getAll(['orderBy' => 'name']);
        $types = $this->schema('Classrooms_Room_Type')->getAll(['orderBy' => 'name']);
        $titles = $this->schema('Classrooms_Software_Title');

        $selectedBuildings = $this->request->getQueryParameter('buildings');
        $selectedTypes = $this->request->getQueryParameter('types');
        $selectedTitles = $this->request->getQueryParameter('titles');
        
		$condition = $locations->deleted->isFalse()->orIf($locations->deleted->isNull());
        if ($selectedBuildings)
        {
            foreach ($selectedBuildings as $selected)
            {
                $query = $locations->buildingId->equals($selected);
                $condition = $condition ? $condition->andIf($query) : $query;
            }
        }
        if ($selectedTypes)
        {
            foreach ($selectedTypes as $selected)
            {
                $query = $locations->typeId->equals($selected);
                $condition = $condition ? $condition->andIf($query) : $query;
            }
        }
        if ($selectedTitles)
        {
            foreach ($selectedTitles as $selected)
            {
                $title = $titles->get($selected);
                $query = $locations->id->inList(array_keys($title->getRoomsUsedIn()));
                $condition = $condition ? $condition->andIf($query) : $query;
            }
        }

        $rooms = $locations->find($condition, ['orderBy' => 'number']);

        $this->template->selectedBuildings = $selectedBuildings;
        $this->template->selectedTypes = $selectedTypes;
        $this->template->selectedTitles = $selectedTitles;
        $this->template->buildings = $buildings;
        $this->template->types = $types;
        $this->template->rooms = $rooms;
        $this->template->titles = $titles->getAll(['orderBy' => 'name']);
        $this->template->allFacets = self::$AllRoomFacets;
        $this->template->hasFilters = $condition;
    }

    public function editConfiguration ()
    {
        $viewer = $this->requireLogin();
        $this->requirePermission('edit room');

        $rooms = $this->schema('Classrooms_Software_Title');
        $configs = $this->schema('Classrooms_Software_License');
        $notes = $this->schema('Classrooms_Notes_Entry');
        $room = $rooms->get($this->getRouteVariable('id'));
        $config = $configs->get($this->getRouteVariable('cid'));

        $this->addBreadcrumb('rooms', 'List Rooms');
        $this->addBreadcrumb('rooms/' . $room->id . '/edit', $room->building->name . ' ' . $room->number);

        if ($this->request->wasPostedByUser())
        {
            switch ($this->getPostCommand())
            {
                case 'save':
                    $config->addNote('Configuration updated', $viewer, $this->request->getPostParameters());
                    $config->absorbData($this->request->getPostParameters());
                    $config->save();

                    $this->flash('Updated');
                    break;

                case 'delete':
                    $config->deleted = true;
                    $config->save();
                    $config->addNote('Configuration deleted', $viewer);

                    $this->flash('Deleted');
                    break;
            }

            $this->response->redirect('rooms/' . $room->id . '/edit');
        }

        $this->template->room = $room;
        $this->template->selectedConfiguration = $config;
        $this->template->notes = $notes->find($notes->path->like($config->getNotePath().'%'), ['orderBy' => '-createdDate']);
    }

    public function downloadImage ()
    {   
    	$roomId = $this->getRouteVariable('id');
    	$fileId = $this->getRouteVariable('fileid');
    	$location = $this->schema('Classrooms_Room_Location')->get($roomId);
    	$this->forward('files/' . $fileId . '/download', ['allowed' => true]);
    }

    public function uploadImages ()
    {
        $viewer = $this->requireLogin();

        if ($this->request->wasPostedByUser())
        {
            $results = [
                'message' => 'Server error when uploading.',
                'status' => 500,
                'success' => false
            ];

            $files = $this->schema('Classrooms_Files_File');
            $file = $files->createInstance();
            $file->createFromRequest($this->request, 'file', false);
    
            if ($file->isValid())
            {
                $uploadedBy = (int)$this->request->getPostParameter('uploadedBy', $viewer->id);
                $roomId = (int)$this->request->getPostParameter('roomId', $this->getRouteVariable('id'));
                $file->uploaded_by_id = $uploadedBy;
                $file->location_id = $roomId;
                $file->moveToPermanentStorage();
                $file->save();
            
                $results = [
                    'message' => 'Your file has been uploaded.',
                    'status' => 200,
                    'success' => true,
                    'file' => [
                        'url' => 'rooms/'.$roomId.'/files/' . $file->id . '/download',
                        'fullUrl' => $this->baseUrl('rooms/'.$roomId.'/files/' . $file->id . '/download'),
                        'name' => $file->remoteName,
                    ],
                ];
            }
            else
            {
                $messages = 'Incorrect file type or file too large.';
                $results['status'] = $messages !== '' ? 400 : 422;
                $results['message'] = $messages;
            }

            echo json_encode($results);
            exit;  
        }    

        $this->template->viewer = $viewer;
    }

    protected function saveRoomBundles ($room, $data)
    {
        $bundles = $this->schema('Classrooms_Room_Configuration');
        $posted = isset($data['bundles']) ? $data['bundles'] : [];
        $existing = $existingBundles = $removed = $added = [];
        
        foreach ($room->configurations->asArray() as $config)
        {
            if ($config->isBundle) $existingBundles[] = $config;
        }

        foreach ($existingBundles as $bundle)
        {
            $existing[$bundle->id] = 'on';
        }

        if ($removed = array_diff_key($existing, $posted))
        {
            foreach ($removed as $key => $on)
            {
                $bundle = $bundles->get($key);
                $room->configurations->remove($bundle);
                $room->configurations->save();
            }
        }

        if ($added = array_diff_key($posted, $existing))
        {
            foreach ($added as $key => $on)
            {
                $bundle = $bundles->get($key);
                $room->configurations->add($bundle);
                $room->configurations->save();
            }          
        }

        $room->save();

        return [$removed, $added];
    }

    protected function saveConfigurationLicenses ($config, $data)
    {
        $licenses = $this->schema('Classrooms_Software_License');
        $existingLicenses = $config->softwareLicenses->asArray();
        $posted = isset($data['licenses']) ? $data['licenses'] : [];
        $existing = $removed = $added = [];
        
        foreach ($existingLicenses as $l)
        {
            $existing[$l->id] = 'on';
        }
        
        if ($removed = array_diff_key($existing, $posted))
        {
            foreach ($removed as $key => $on)
            {
                $license = $licenses->get($key);
                $config->softwareLicenses->remove($license);
                $config->softwareLicenses->save();
            }
        }

        if ($added = array_diff_key($posted, $existing))
        {
            foreach ($added as $key => $on)
            {
                $license = $licenses->get($key);
                $config->softwareLicenses->add($license);
                $config->softwareLicenses->save();
            }                       
        }

        $config->save();

        return [$removed, $added];
    }
}
