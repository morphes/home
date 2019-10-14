CKEDITOR.plugins.add('buttonh3',{
	init:function(editor){
		var command = new CKEDITOR.command( editor,
		{
			exec : function( editor )
			{
				var format = {
					element : "h3"
				};
				var style = new CKEDITOR.style(format);
				style.apply(editor.document);
			}
		});

		var command = editor.addCommand('buttonh3',command);
		command.modes = {wysiwyg:1, source:1};
		command.canUndo = true;

		editor.ui.addButton("buttonH3",{
			label:"Button H3",
			icon: this.path + "H3.png",
			command:'buttonh3'
		});
	}
});