/**
 * Archives Widget Extended — archive dropdown navigation.
 *
 * Initialises every <select data-aexws-dropdown> on the page so that
 * selecting a month navigates to that archive URL, mirroring the
 * behaviour of the core WP_Widget_Archives dropdown.
 */

function initDropdown( dropdown ) {
	function onSelectChange() {
		setTimeout( () => {
			if ( 'escape' === dropdown.dataset.lastkey ) {
				return;
			}
			if ( dropdown.value ) {
				document.location.href = dropdown.value;
			}
		}, 250 );
	}

	function onKeyUp( event ) {
		if ( 'Escape' === event.key ) {
			dropdown.dataset.lastkey = 'escape';
		} else {
			delete dropdown.dataset.lastkey;
		}
	}

	function onClick() {
		delete dropdown.dataset.lastkey;
	}

	dropdown.addEventListener( 'keyup', onKeyUp );
	dropdown.addEventListener( 'click', onClick );
	dropdown.addEventListener( 'change', onSelectChange );
}

document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( 'select[data-aexws-dropdown]' ).forEach( initDropdown );
} );