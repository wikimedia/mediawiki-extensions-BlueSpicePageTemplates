( function () {
	const $pageHeader = $( '#bs-pt-head' );
	if ( !$pageHeader.length ) {
		return;
	}

	let enabledContainer = 'Tags';
	const $nsContainer = $( '#bs-ns-container' );
	const $tagContainer = $( '#bs-tag-container' );
	const $nsContainers = $nsContainer.find( '.bs-ns-subsect, .bs-pt-item' );
	const $tagContainers = $tagContainer.find( '.bs-tag-subsect, .bs-pt-item' );

	const showTagContainer = function () {
		$nsContainer.fadeOut(); // eslint-disable-line no-jquery/no-fade
		$tagContainer.fadeIn(); // eslint-disable-line no-jquery/no-fade
	};

	const showNsContainer = function () {
		$tagContainer.fadeOut(); // eslint-disable-line no-jquery/no-fade
		$nsContainer.fadeIn(); // eslint-disable-line no-jquery/no-fade
	};

	const hideTags = function ( inputValue ) {
		let tagsMatches = false;
		const filteredContainers = {};
		let $containers = $nsContainers;
		if ( enabledContainer === 'Tags' ) {
			$containers = $tagContainers;
		}

		// Find all containers that match the input value
		Object.entries( $containers ).forEach( ( [ index, currentContainer ] ) => {
			const $currentContainer = $( currentContainer ),
				lastContainer = Object.keys( filteredContainers ).pop();

			let show;
			if ( $currentContainer.hasClass( 'bs-tag-subsect' ) ||
				$currentContainer.hasClass( 'bs-ns-subsect' ) ) {
				const containerName = $currentContainer.find( 'h3' ).text();
				show = containerName.toLowerCase().includes( inputValue.replace( /\s/g, '_' ) );
				filteredContainers[ containerName ] = {
					templates: {},
					show: show,
					index: index
				};
			} else if ( $currentContainer.hasClass( 'bs-pt-item' ) ) {
				// enables also filtering by description
				const elementText = $currentContainer.text().toLowerCase();

				show = elementText.includes( inputValue );
				if ( lastContainer ) {
					filteredContainers[ lastContainer ].templates[ index ] = {
						show: show,
						index: index
					};
				}
			}
		} );

		// set tag visible when one of child templates match the input value
		Object.entries( filteredContainers ).forEach( ( [ index, container ] ) => { // eslint-disable-line no-unused-vars
			if ( container.show ) {
				Object.entries( container.templates ).forEach( ( [ templateKey, template ] ) => { // eslint-disable-line no-unused-vars
					template.show = true;
				} );
			} else {
				Object.entries( container.templates ).forEach( ( [ templateKey, template ] ) => { // eslint-disable-line no-unused-vars
					if ( template.show ) {
						container.show = true;
					}
				} );
			}
		} );

		// update show properties of all containers
		Object.entries( filteredContainers ).forEach( ( [ key, container ] ) => { // eslint-disable-line no-unused-vars
			Object.entries( container.templates ).forEach( ( [ templateKey, template ] ) => { // eslint-disable-line no-unused-vars
				if ( template.show ) {
					$containers.eq( template.index ).fadeIn( 150 ); // eslint-disable-line no-jquery/no-fade
				} else {
					$containers.eq( template.index ).fadeOut( 100 ); // eslint-disable-line no-jquery/no-fade
				}
			} );

			if ( container.show ) {
				$containers.eq( container.index ).fadeIn( 150 ); // eslint-disable-line no-jquery/no-fade
			} else {
				$containers.eq( container.index ).fadeOut( 100 ); // eslint-disable-line no-jquery/no-fade
			}
			tagsMatches = tagsMatches || container.show;
		} );

		// set notification when no template, tag or ns found
		$pageHeader.find( 'label' ).remove();
		if ( !tagsMatches ) {
			$pageHeader.append( '<label>' + mw.message( 'bs-pagetemplates-no-matching-results-label' ).text() +
			'</label>' );
		}
	};

	const searchField = OO.ui.infuse( '#bs-template-search-input' );
	searchField.connect( searchField, {
		change: function () {
			hideTags( searchField.value.toLowerCase() );
		}
	} );

	const tagButton = OO.ui.infuse( '#bs-template-search-tag-button' );
	tagButton.connect( tagButton, {
		click: function () {
			enabledContainer = tagButton.label;

			nsButton.setActive( false ); // eslint-disable-line no-use-before-define
			tagButton.setActive( true );

			hideTags( searchField.value.toLowerCase() );
			showTagContainer();
		}
	} );

	var nsButton = OO.ui.infuse( '#bs-template-search-ns-button' ); // eslint-disable-line no-var
	nsButton.connect( nsButton, {
		click: function () {
			enabledContainer = nsButton.label;

			nsButton.setActive( true );
			tagButton.setActive( false );

			hideTags( searchField.value.toLowerCase() );
			showNsContainer();
		}
	} );
}( mediaWiki ) );
