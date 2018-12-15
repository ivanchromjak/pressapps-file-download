( function() {  
	tinymce.PluginManager.add( 'pa_file', function( editor, url ) {
		editor.addButton( 'pa_file', {
			text    : editor.getLang( 'pafd.insertFile' )
			, id    : 'pafd-editor-button'
			, width : 200
			, type  : 'listbox'
			, menu  : [ 
				{
					text: editor.getLang( 'pafd.singleFile' )
					, onclick: function() { 
						editor.windowManager.open( {
							title: editor.getLang( 'pafd.chooseFiles' )
							, url: ajaxurl + '?action=pafd_files_dropdown'
						} );
					}
				}
				, {
					text: editor.getLang( 'pafd.fileCategoryOrCategories' )
					, onclick: function() { 
						editor.windowManager.open( {
							title: editor.getLang( 'pafd.chooseCategories' )
							, url: ajaxurl + '?action=pafd_categories_dropdown'
						} );
					}
				}
			]
		} );
	} );
} )();