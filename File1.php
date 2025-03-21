modules/custom/mymodule/mymodule.ckeditor5.yml

myplugin:
  id: myplugin
  label: 'My Custom Plugin'
  description: 'Adds a custom button to CKEditor 5.'
  class: '\Drupal\mymodule\Plugin\CKEditor5Plugin\MyPlugin'
  library: 'mymodule/myplugin'
  dependencies: []


modules/custom/mymodule/src/Plugin/CKEditor5Plugin/MyPlugin.php

<?php

namespace Drupal\mymodule\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\CKEditor5PluginBase;

/**
 * Defines the "MyPlugin" plugin.
 *
 * @CKEditor5Plugin(
 *   id = "myplugin",
 *   label = @Translation("My Custom Plugin"),
 *   module = "mymodule"
 * )
 */
class MyPlugin extends CKEditor5PluginBase {
}




modules/custom/mymodule/js/plugins/myplugin.js

import { Plugin } from '@ckeditor/ckeditor5-core';
import { ButtonView } from '@ckeditor/ckeditor5-ui';

export default class MyPlugin extends Plugin {
  init() {
    const editor = this.editor;

    // Register the button in the UI
    editor.ui.componentFactory.add('myplugin', (locale) => {
      const button = new ButtonView(locale);

      button.set({
        label: 'Insert Custom Text',
        withText: true,
        tooltip: true
      });

      // Define what happens when the button is clicked
      button.on('execute', () => {
        editor.model.change((writer) => {
          const insertPosition = editor.model.document.selection.getFirstPosition();
          writer.insertText('[custom-tag]', insertPosition);
        });
      });

      return button;
    });
  }
}





modules/custom/mymodule/mymodule.libraries.yml

myplugin:
  version: 1.x
  js:
    js/plugins/myplugin.js: { type: module }
  dependencies:
    - core/ckeditor5