## An Important Note About Support

DropDate (in common with all of my ExpressionEngine add-ons) is no longer officially supported.

Check out the wiki for [installation and usage instructions][wiki], and feel free to fork the repo if you'd like to make some changes or improvements (it's distributed under a liberal open source license).

Hopefully this will be everything you need to use this add-on in your projects, but if not please don't email me asking for support; I don't even have ExpressionEngine installed locally any more.

[wiki]: https://github.com/experience/dropdate.ee_addon/wiki/ "View the documentation"

## Overview

ExpressionEngine Fieldtype enabling users to select a date using 3 drop-downs (day, month, year).

Each field can be saved as a [UNIX timestamp][unix], or in `YYYYMMDD` format, for [Super Search][super_search] compatibility.

[unix]: http://en.wikipedia.org/wiki/Unix_time "Read more about the UNIX timestamp"
[super_search]: http://www.solspace.com/software/detail/super_search/ "Read more about Solspace's Super Search module"

## Bigger, Faster, Better, Stronger

If this is a new site, your best bet is to use the version of DropDate in the `develop` branch. It's a complete rewrite, and resolves a few minor bugs.

The reason it never made it to the `master` branch is that I didn't get around to writing a migration script, to convert existing DropDate fields to the new version. As such, it's no good if you've already got a bunch of data stored in DropDate fields.
