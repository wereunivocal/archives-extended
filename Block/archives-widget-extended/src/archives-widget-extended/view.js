/**
 * Front-end handler for the Archives Widget Extended dropdown variant.
 *
 * Each archive dropdown is marked with `data-aex-archive-dropdown="1"` by the
 * PHP renderer. Navigating to the selected archive URL on `change` mirrors
 * the behaviour of the core/archives block, with Escape suppressing the
 * navigation so keyboard users can dismiss without redirecting.
 */
function bindDropdown( select ) {
	if ( select.dataset.aexBound === '1' ) {
		return;
	}
	select.dataset.aexBound = '1';

	select.addEventListener( 'keyup', ( event ) => {
		if ( event.key === 'Escape' ) {
			select.dataset.lastkey = 'escape';
		} else {
			delete select.dataset.lastkey;
		}
	} );

	select.addEventListener( 'click', () => {
		delete select.dataset.lastkey;
	} );

	select.addEventListener( 'change', () => {
		setTimeout( () => {
			if ( select.dataset.lastkey === 'escape' ) {
				return;
			}
			if ( select.value ) {
				window.location.href = select.value;
			}
		}, 250 );
	} );
}

function init() {
	document
		.querySelectorAll( 'select[data-aex-archive-dropdown="1"]' )
		.forEach( bindDropdown );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
