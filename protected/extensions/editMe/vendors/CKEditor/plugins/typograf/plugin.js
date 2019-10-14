CKEDITOR.plugins.add('typograf', {
  init : function(editor) {
      
    var command = new CKEDITOR.command( editor,
    {
        exec : function( editor )
        {
            $.post('/content/typograf', {content:editor.document.getBody().getHtml()}, function(data){
                editor.document.getBody().setHtml(data);
                alert('Текст отформатирован');
            });
        }
    });
      
      
    var command = editor.addCommand('typograf', command);
    command.modes = {wysiwyg:1, source:1};
    command.canUndo = true;

    editor.ui.addButton('Typograf', {
      label : 'Типограф',
      command : 'typograf',
      icon: this.path + 'images/typograf.gif'
    });
  }
});

