// Docu : http://wiki.moxiecode.com/index.php/TinyMCE:Create_plugin/3.x#Creating_your_own_plugins
// by redcocker
// Last modified: 2011/10/22

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('tinymce_backslash');
	 
	tinymce.create('tinymce.plugins.tinymce_backslash', {
		
		init : function(ed, url) {
		// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

			// Register example button
			ed.addButton('tinymce_backslash', {
				title : 'TinyMCE Backslash Button',
				cmd : 'tinymce_backslash_cmd',
				image : url + '/backslash.png'
			});

			ed.addCommand('tinymce_backslash_cmd', function() {
				var styled_backslash = '<span style="font-family: \'Courier New\',Courier,monospace;">\\</span>'
				ed.execCommand('mceInsertContent', false, styled_backslash);
			});

		},

                createControl : function(n, cm) {
			return null;
		},

		getInfo : function() {
			return {
					longname  : 'TinyMCE Backslash Button',
					author 	  : 'redcocker',
					authorurl : 'http://www.near-mint.com/blog',
					infourl   : 'http://www.near-mint.com/blog',
					version   : "0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('tinymce_backslash', tinymce.plugins.tinymce_backslash);
})();