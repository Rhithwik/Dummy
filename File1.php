Here's the full code to create a custom Drupal 10 module named sce_ckeditor_customization that adds a CKEditor 5 plugin for inserting a <showmore> tag.


---

1. Create the Module Structure

Create the following directory structure under modules/custom/sce_ckeditor_customization/:

sce_ckeditor_customization/
│── config/install/
│   ├── ckeditor5.plugin.showmore.yml
│── icons/
│   ├── showmore.svg
│── js/plugins/
│   ├── showmore.js
│── sce_ckeditor_customization.info.yml
│── sce_ckeditor_customization.libraries.yml
│── sce_ckeditor_customization.module


---

2. Define the Module Info File

Create the module definition file.

File: sce_ckeditor_customization.info.yml

name: 'SCE CKEditor Customization'
type: module
description: 'Adds a custom CKEditor 5 plugin to insert <showmore> tags.'
package: Custom
core_version_requirement: ^10
dependencies:
  - drupal:ckeditor5


---

3. Register the Plugin with CKEditor

Define the CKEditor 5 plugin configuration.

File: config/install/ckeditor5.plugin.showmore.yml

langcode: en
status: true
dependencies:
  module:
    - sce_ckeditor_customization
id: showmore
label: 'Show More Plugin'
description: 'Wraps selected text with <showmore> tag.'
provider: sce_ckeditor_customization
plugin: showmore
library: sce_ckeditor_customization/showmore_plugin


---

4. Define the Library

Register the JavaScript file and icon.

File: sce_ckeditor_customization.libraries.yml

showmore_plugin:
  js:
    js/plugins/showmore.js: {}
  dependencies:
    - core/drupal
    - core/ckeditor5
  images:
    icons/showmore.svg: {}


---

5. Create the CKEditor Plugin

Implement the CKEditor 5 plugin.

File: js/plugins/showmore.js

import { Plugin } from '@ckeditor/ckeditor5-core';
import { ButtonView } from '@ckeditor/ckeditor5-ui';
import showMoreIcon from '../../icons/showmore.svg'; // Import the SVG icon

export default class ShowMorePlugin extends Plugin {
    init() {
        const editor = this.editor;

        editor.ui.componentFactory.add('showMore', locale => {
            const button = new ButtonView(locale);

            button.set({
                label: 'Show More',
                icon: showMoreIcon, // Use the imported SVG icon
                tooltip: true
            });

            button.on('execute', () => {
                const model = editor.model;
                const selection = model.document.selection;
                const selectedText = model.getSelectedContent(selection);

                model.change(writer => {
                    if (!selectedText.isEmpty) {
                        const showMoreElement = writer.createElement('showMore');
                        writer.append(selectedText, showMoreElement);
                        model.insertContent(showMoreElement);
                    }
                });
            });

            return button;
        });

        // Define schema for <showmore>
        editor.model.schema.extend('$text', { allowIn: 'showMore' });
        editor.model.schema.register('showMore', {
            allowWhere: '$block',
            allowContentOf: '$block',
            isInline: true
        });

        // Conversion: Model <-> View
        editor.conversion.elementToElement({
            model: 'showMore',
            view: 'showmore'
        });

        editor.conversion.for('editingDowncast').elementToElement({
            model: 'showMore',
            view: (modelElement, { writer }) => writer.createContainerElement('showmore', { class: 'show-more' })
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

6. Add the Icon

Place an SVG icon for the button.

File: icons/showmore.svg

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
  <path d="M12 4L4 12h16L12 4zm0 16l8-8H4l8 8z"/>
</svg>


---

7. Clear Cache & Enable the Plugin

After adding these files, clear caches and enable the module:

drush en sce_ckeditor_customization -y
drush cr


---

8. Enable the Plugin in CKEditor 5

1. Go to Admin → Configuration → Content Authoring → Text formats and editors.


2. Edit the text format using CKEditor 5 (e.g., "Full HTML").


3. Enable the "Show More Plugin" under Available Plugins.


4. Drag the "Show More" button into the toolbar.


5. Save the configuration.




---

9. Test the Plugin

Open a CKEditor 5 field.

Select some text.

Click the Show More button.

The text should be wrapped in <showmore></showmore>.


Now, your custom Drupal 10 module successfully adds a CKEditor 5 button that wraps selected text in a <showmore> tag!

Would you like any improvements, such as styling or additional functionality?

