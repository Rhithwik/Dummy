To create a CKEditor 5 plugin for Drupal 10 that adds a <showmore></showmore> tag around selected text, follow these steps:


---

1. Create the CKEditor Plugin

First, define a JavaScript file for the CKEditor 5 plugin inside your custom module.

File: modules/custom/my_module/js/plugins/showmore.js

import { Plugin } from '@ckeditor/ckeditor5-core';
import { ButtonView } from '@ckeditor/ckeditor5-ui';
import { findOptimalInsertionPosition } from '@ckeditor/ckeditor5-widget';

export default class ShowMorePlugin extends Plugin {
    init() {
        const editor = this.editor;

        // Add a new UI button
        editor.ui.componentFactory.add('showMore', locale => {
            const button = new ButtonView(locale);

            button.set({
                label: 'Show More',
                withText: true,
                tooltip: true
            });

            button.on('execute', () => {
                const model = editor.model;
                const selection = model.document.selection;
                const selectedText = model.getSelectedContent(selection);

                model.change(writer => {
                    // Wrap selected text with <showmore>
                    if (!selectedText.isEmpty) {
                        const showMoreElement = writer.createElement('showMore');
                        writer.append(selectedText, showMoreElement);

                        const position = findOptimalInsertionPosition(selection, model);
                        model.insertContent(showMoreElement, position);
                    }
                });
            });

            return button;
        });

        // Schema definition for <showmore>
        editor.model.schema.extend('$text', { allowIn: 'showMore' });
        editor.model.schema.register('showMore', {
            allowWhere: '$block',
            allowContentOf: '$block',
            isInline: true
        });

        // Conversion
        editor.conversion.elementToElement({
            model: 'showMore',
            view: 'showmore'
        });

        editor.conversion.for('editingDowncast').elementToElement({
            model: 'showMore',
            view: (modelElement, { writer }) => {
                return writer.createContainerElement('showmore', {
                    class: 'show-more'
                });
            }
        });

        editor.conversion.for('dataDowncast').elementToElement({
            model: 'showMore',
            view: 'showmore'
        });

        editor.conversion.for('upcast').elementToElement({
            view: 'showmore',
            model: 'showMore'
        });
    }
}


---

2. Register the Plugin in Drupal

Now, register this plugin in a CKEditor 5 configuration YAML file.

File: modules/custom/my_module/config/install/ckeditor5.plugin.showmore.yml

langcode: en
status: true
dependencies:
  module:
    - my_module
id: showmore
label: 'Show More Plugin'
description: 'Wraps selected text with <showmore> tag.'
provider: my_module
plugin: showmore
library: my_module/showmore_plugin


---

3. Define the Library

Add the necessary JavaScript library to the module.

File: modules/custom/my_module/my_module.libraries.yml

showmore_plugin:
  js:
    js/plugins/showmore.js: {}
  dependencies:
    - core/drupal
    - core/ckeditor5


---

4. Enable the Plugin in Drupal

After adding the plugin, configure CKEditor 5 to use it:

1. Go to Admin → Configuration → Content Authoring → Text formats and editors.


2. Edit your CKEditor 5-enabled text format (e.g., "Full HTML").


3. Under Available Plugins, enable Show More Plugin.


4. Drag the Show More button into the toolbar.


5. Save the configuration.




---

5. Clear Cache & Test

Run the following command to clear caches:

drush cr

Now, when you select text in CKEditor 5 and click the "Show More" button, it will wrap the selected text with <showmore></showmore>.

Would you like any modifications, such as additional styling or behavior?

