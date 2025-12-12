/**
 * @license Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
  config.versionCheck = false;
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';  

  config.filebrowserBrowseUrl = "/elfinder/ckeditor";

  CKEDITOR.plugins.addExternal(
    "ckeditor_wiris",
    "/ckeditor/plugins/ckeditor_wiris/mathtype-ckeditor4/",
    "plugin.js"
  );

  config.extraPlugins = "ckeditor_wiris";
  config.allowedContent = true;

  // The toolbar groups arrangement, optimized for a single toolbar row.
	config.toolbarGroups = [
		{ name: 'basicstyles', groups: [ 'basicstyles' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'styles', groups: [ 'format', 'fontsize' ] },
		{ name: 'tools' },
		{ name: 'others' }
	];
};

CKEDITOR.config.htmlEncodeOutput
CKEDITOR.config.entities 