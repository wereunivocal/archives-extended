/**
 * Registers the Archives Widget Extended block. The block is server-rendered, so
 * no `save` function is provided — the editor preview is driven by
 * ServerSideRender (see ./edit.js) and the front end by the PHP render
 * callback in Blocks/class-archivesextendedblock.php.
 */
import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
} );
