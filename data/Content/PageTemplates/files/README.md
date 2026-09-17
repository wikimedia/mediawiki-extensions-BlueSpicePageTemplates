# Page template preview images

Images in this directory are shown in the page template selection list. They are
resolved by convention only - nothing has to be registered, configured or stored:

* An image belongs to the template of the same name, e.g. `PT_How-to_article.svg`
  for `Template:PT_How-to_article`. Subpage templates may use subdirectories.
* `generic.svg` is used for every template without an image of its own.
* `empty.svg` is used for the "empty page" entry of the selection list.
* Only `.svg` files are picked up.

Any extension that ships page templates can provide previews the same way: put a
`files` directory next to the content manifest that is registered in the
`DefaultPageTemplates` manifest list. Extensions are searched in registration
order, this extension is searched last.

See `BlueSpice\PageTemplates\PreviewImageLookup`.
