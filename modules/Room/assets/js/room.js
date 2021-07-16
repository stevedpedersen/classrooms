(function ($) {
  $(function () {
    $('.room-pills a').on('click', function(e) {
      window.history.replaceState({}, null, ($(e.target).attr('href')));
    });

  $('.row-expand-btn').on('click', function(e) {
  	var target = $($(this).attr('href'));
  	if (!target.hasClass('in')) {
  		$(this).text('- Hide all versions');
  	} else {
  		$(this).text('+ Show all versions');
  	}
  });

    // AUTO-COMPLETE
	var autoCompleteUrl = document.baseURI + 'rooms/autocomplete';

	var filterRooms = function (data, term, error = false) {
		$('.loader').hide();
		var ids = new Array;
		for (const id in data) {
			ids.push(id);
		}

		$('.room-card').each(function (i, item) {
			if (ids.includes($(item).attr('id'))) {
				$(this).show(500);
			} else {
				$(this).hide(500);
			}
		});

		$('#regular-results').hide();
		if (error) {
			$('#js-results').show().text(`No results for "${term}"`);
		} else {

			$('#js-results').show().text(`Showing results for "${term}"`);
		}
	}

	$('.autocomplete').autocomplete({
		delay: 50,
		minLength: 2,
		// appendTo: ".search-container",
		source: function (request, response) {
			var term = request.term;

			if (term.length > 1) {
				// $('.loader').show();
				$.ajax(autoCompleteUrl + '?s=' + term, {
					type: 'get',
					dataType: 'json',
					success: function (o) {
						switch (o.status) {
							case 'success':
								response(filterRooms(o.data, term));
								break;
							case 'error':
								response(filterRooms(o.data, term, true));
								break;
							default:
								console.log('unknown error');
								break;
						}
					}
				});
			}
			else
			{
				// console.log('deleted query');
				$('.room-card').show(500);
			}
		}
	});

	$('.autocomplete').keyup(function(e){
	    if (e.keyCode == 8) {
	        if ($(this).val() === "") {
	        	$('#regular-results').hide();
	        	$('#js-results').hide();
	        	$('.room-card').show(500);
	        	$('.loader').hide();
	        } 
	    }
	});



    var autoCompleteAccountsUrl = $('base').attr('href') + 'schedules/autocomplete';

    var transformAccounts = function (data) {
      var results = [];

      for (var id in data) {
        var info = data[id];
        results.push({
          value: id,
          username: info.username,
          label: info.firstName + ' ' + info.lastName + ' (' + info.username + ')' 
        });
      }

      return results;
    };

    $('.account-autocomplete').autocomplete({
      delay: 200,
      minLength: 3,
      appendTo: ".search-container",
      source: function (request, response) {
        var term = request.term;
        var semester = $('#selectedTerm').val();
        if (term.length > 2) {
          $.ajax(`${autoCompleteAccountsUrl}?s=${term}&sem=${semester}`, {
            type: 'get',
            dataType: 'json',
            success: function (o) {
              switch (o.status) {
                case 'success':
                  response(transformAccounts(o.data));
                  break;
                case 'error':
                  //console.log(o.message);
                  break;
                default:
                  //console.log('unknown error');
                  break;
              }
            }
          });
        }
      },
      select: function (event, ui) {
        event.stopPropagation();
        event.preventDefault();
        var item = ui.item;
        var $self = $(this);
        var shadowId = this.id + '-shadow';
        var $shadow = $('#' + shadowId);

        if ($shadow.length === 0) {
          $shadow = $('<input type="hidden" name="u">');
          $shadow.attr('id', shadowId);
          $('#autcompleteContainer').prepend($shadow);
        }

        $shadow.attr('value', item.username); // search by username
        this.value = item.label;
      }
    });

  });
})(jQuery);