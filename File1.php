In Drupal 10, CKEditor 5 plugins are registered using a combination of module configuration, YAML files, and JavaScript. Here's how a plugin is typically registered:


---

1. Define the CKEditor Plugin in a JavaScript File

Create a JavaScript file for your plugin inside your module, for example:

modules/custom/sce_ckeditor_customization/js/plugins/showmore.js

This JavaScript file should define and register the plugin using CKEditor 5's API:

import { Plugin } from '@ckeditor/ckeditor5-core';
import { ButtonView } from '@ckeditor/ckeditor5-ui';

export default class ShowMorePlugin extends Plugin {
  init() {
    const editor = this.editor;

    editor.ui.componentFactory.add('showMore', (locale) => {
      const button = new ButtonView(locale);

      button.set({
        label: 'Insert ShowMore',
        withText: true,
        tooltip: true
      });

      button.on('execute', () => {
        editor.model.change((writer) => {
          const insertPosition = editor.model.document.selection.getFirstPosition();
          writer.insertText('<showmore>', insertPosition);
        });
      });

      return button;
    });
  }
}


---

2. Register the Plugin in ckeditor5.plugins.yml

In your module, create a file:

modules/custom/sce_ckeditor_customization/config/schema/ckeditor5.plugins.yml

Define your plugin like this:

sce_ckeditor_customization_showmore:
  id: showmore
  label: 'ShowMore Button'
  description: 'Adds a button to insert a <showmore> tag.'
  class: '\Drupal\sce_ckeditor_customization\Plugin\CKEditor5Plugin\ShowMore'
  library: 'sce_ckeditor_customization/showmore'
  dependencies: []


---

3. Create the CKEditor Plugin Class in PHP

Define a PHP class to register the plugin with Drupal:

modules/custom/sce_ckeditor_customization/src/Plugin/CKEditor5Plugin/ShowMore.php

<?php

namespace Drupal\sce_ckeditor_customization\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\CKEditor5PluginBase;

/**
 * Defines the "ShowMore" plugin.
 *
 * @CKEditor5Plugin(
 *   id = "showmore",
 *   label = @Translation("ShowMore"),
 *   module = "sce_ckeditor_customization"
 * )
 */
class ShowMore extends CKEditor5PluginBase {

}


---

4. Define a Drupal Library

Create a libraries.yml file inside your module:

modules/custom/sce_ckeditor_customization/sce_ckeditor_customization.libraries.yml

showmore:
  version: 1.x
  js:
    js/plugins/showmore.js: { type: module }
  dependencies:
    - core/ckeditor5


---

5. Enable the Plugin in a Text Format

1. Go to Configuration > Content Authoring > Text formats and editors (/admin/config/content/formats).


2. Edit the text format where you want the plugin enabled (e.g., "Full HTML").


3. Find your plugin (it should be listed as "ShowMore") and enable it.


4. Save the changes.




---

6. Clear Cache

Run:

drush cr

Or go to Configuration > Performance and clear the cache.


---

How It Works

The JavaScript file defines the plugin behavior.

The YAML and PHP files register the plugin with Drupal.

The plugin is added to CKEditor when the text format is used.


Now, when editing content, the "ShowMore" button should appear in the CKEditor toolbar.

