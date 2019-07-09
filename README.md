# SilverStripe Elemental Video (+ Text) Block

A block that displays content with one or multiple Images.  
(private project, no help/support provided)

## Requirements

* SilverStripe CMS ^4.3
* dnadesign/silverstripe-elemental ^4.0
* sheadawson/silverstripe-linkable ^2.0@dev
* jonom/focuspoint ^3.0
* bummzack/sortablefile ^2.0



## Suggestions
* derralf/elemental-styling

Modify `/templates/Derralf/Elements/TextImages/Includes/Title.ss` to your needs when using StyledTitle from derralf/elemental-styling.


## Installation

- Install the module via Composer
  ```
  composer require derralf/elemental-video
  ```


## Configuration

A basic/default config. Add this to your **mysite/\_config/elements.yml**

Optionally you may set `defaults:Style`to any of the available `styles`.

```

---
Name: elementaltextimages
---
Derralf\Elements\Video\Element\ElementVideo:
  styles:
    '': "Standard"
    VideoLeftFiftyFifty: "Video links, 50%"
    VideoRightFiftyFifty: "Video rechts, 50%"
    VideoRightThirtythree: "Video rechts, 33%"
  styles_default_description: 'Standard: volle Breite, Text darüber'
  readmore_link_class: 'btn btn-primary btn-readmore'
```

Additionally you may apply the default styles:

```
# add default styles
DNADesign\Elemental\Controllers\ElementController:
  default_styles:
    - derralf/elemental-video:client/dist/styles/frontend-default.cs
```

See Elemental Docs for [how to disable the default styles](https://github.com/dnadesign/silverstripe-elemental#disabling-the-default-stylesheets).


