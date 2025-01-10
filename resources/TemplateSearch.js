( function() {
	var $pageHeader = $( '#bs-pt-head' );
	if ( !$pageHeader.length ) {
		return;
	}

	var enabledContainer = 'Tags';
		$nsContainer = $( '#bs-ns-container' ),
		$tagContainer = $( '#bs-tag-container' ),
		$nsContainers = $nsContainer.find( '.bs-ns-subsect, .bs-pt-item' ),
		$tagContainers = $containers = $tagContainer.find( '.bs-tag-subsect, .bs-pt-item' );

	var showTagContainer = function() {
		$nsContainer.fadeOut();
		$tagContainer.fadeIn();
	};

	var showNsContainer = function() {
		$tagContainer.fadeOut();
		$nsContainer.fadeIn();
	};

	var hideTags = function( inputValue ) {
		var tagsMatches = false;
			filteredContainers = {};
		var $containers = $nsContainers;
		if ( enabledContainer === 'Tags' ) {
			$containers = $tagContainers;
		}

		// Find all containers that match the input value
		Object.entries( $containers ).forEach( ( [ index, currentContainer ] ) => {
			var $currentContainer = $( currentContainer ),
				lastContainer = Object.keys( filteredContainers ).pop();

			if ( $currentContainer.hasClass( 'bs-tag-subsect' ) ||
				$currentContainer.hasClass( 'bs-ns-subsect' ) ) {
				var containerName = $currentContainer.find( "h3" ).text(),
					show = containerName.toLowerCase().includes( inputValue.replace( /\s/g, "_" ) );
				filteredContainers[ containerName ] = {
					templates: {},
					show: show,
					index: index
				};
			} else if ( $currentContainer.hasClass( 'bs-pt-item' ) ) {
				// enables also filtering by description
				var elementText = $currentContainer.text().toLowerCase();

				show = elementText.includes( inputValue );
				if ( lastContainer ) {
					filteredContainers[ lastContainer ].templates[ index ] = {
						show: show,
						index: index
					};
				}
			}
		});

		// set tag visible when one of child templates match the input value
		Object.entries( filteredContainers ).forEach( ( [ index, container ] ) => {
			if ( container.show ) {
				Object.entries( container.templates ).forEach( ( [ templateKey, template ] ) => {
					template.show = true;
				});
			} else {
				Object.entries( container.templates ).forEach( ( [ templateKey, template ] ) => {
					if ( template.show ) {
						container.show = true;
					}
				});
			}
		} );

		// update show properties of all containers
		Object.entries( filteredContainers ).forEach( ( [ key, container ] ) => {
			Object.entries( container.templates ).forEach( ( [ templateKey, template ] ) => {
				if ( template.show ) {
					$containers.eq( template.index ).fadeIn( 150 );
				} else {
					$containers.eq( template.index ).fadeOut( 100 );
				}
			});

			if ( container.show ) {
				$containers.eq( container.index ).fadeIn( 150 );
			} else {
				$containers.eq( container.index ).fadeOut( 100 );
			}
			tagsMatches = tagsMatches || container.show;
		} );

		// set notification when no template, tag or ns found
		$pageHeader.find( 'label' ).remove();
		if ( !tagsMatches ) {
			$pageHeader.append( '<label>' + mw.message( 'bs-pagetemplates-no-matching-results-label' ).plain() +
			'</label>' );
		}
	};

	var searchField = OO.ui.infuse( '#bs-template-search-input' );
	searchField.connect( searchField, {
		'change': function() {
			hideTags( searchField.value.toLowerCase() );
		}
	} );

	var tagButton = OO.ui.infuse( '#bs-template-search-tag-button' );
	tagButton.connect( tagButton, {
		'click': function() {
			enabledContainer = tagButton.label;

			nsButton.setActive( false );
			tagButton.setActive( true );

			hideTags( searchField.value.toLowerCase() );
			showTagContainer();
		}
	} );

	var nsButton = OO.ui.infuse( '#bs-template-search-ns-button' );
	nsButton.connect( nsButton, {
		'click': function() {
			enabledContainer = nsButton.label;

			nsButton.setActive( true );
			tagButton.setActive( false );

			hideTags( searchField.value.toLowerCase() );
			showNsContainer();
		}
	} );
} ) ( mediaWiki );
