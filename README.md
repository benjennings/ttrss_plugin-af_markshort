# af_markshort (ttrss)

Tiny Tiny RSS Plugin to perform a word count on an article body. Once a short article has been found, where short is defined as 500 words or less, the text [short] is appended to the title of the article. This may then be filtered out or processed using `\[short\]`.

This form of filtering is handy for noisy feeds which reblog or summarise news stories but which also contain longer form articles of interest.

# Installation

Copy into your ttrss plugins folder and add desired feeds in JSON format into the ttrss preferences.

# Dependancies

This plugin uses [html2text.php](http://journals.jevon.org/users/jevon-phd/entry/19818)