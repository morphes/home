CKEDITOR.plugins.add('cuttable', {
  init : function(editor) {
    var command = editor.addCommand('cuttable', new CKEDITOR.dialogCommand('cuttable'));
    command.modes = {wysiwyg:1, source:1};
    command.canUndo = true;

    editor.ui.addButton('Cuttable', {
      label : 'Под кат',
      command : 'cuttable',
      icon: this.path + 'images/cuttable.png'
    });
  }
});


CKEDITOR.dialog.add('cuttable', function(editor) {
  return {
    title : 'Под кат',
    minWidth : 400,
    minHeight : 200,
    onOk: function() {
      var cuttext = this.getContentElement( 'cut', 'cuttext').getInputElement().getValue();
      this._.editor.insertHtml('<myhomecut>' + cuttext + '</myhomecut>');
      this._.editor.insertHtml('<p></p>');
    },
    onChancel: function() {
        
    },
    contents : [{
      id : 'cut',
      label : 'First Tab',
      title : 'First Tab',
      elements : [{
        id : 'cuttext',
        type : 'text',
        label : 'Текст ссылки'
      }]
    }]
  };
});
