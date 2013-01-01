# README
ExpressionEngine Fieldtype enabling users to select a date using 3 drop-downs
(day, month, year).

Each field can be saved as a [UNIX timestamp][unix], or in `YYYYMMDD` format,
for [Super Search][super_search] compatibility.

See [Wiki][wiki] for further information about usage.

[unix]: http://en.wikipedia.org/wiki/Unix_time "Read more about the UNIX timestamp"
[super_search]: http://www.solspace.com/software/detail/super_search/ "Read more about Solspace's Super Search module"
[wiki]: https://github.com/experience/dropdate.ee_addon/wiki/_pages "DropDown Wiki"

## Template Tags: Basic Usage
```
{dropdate}                      // 98960400
{dropdate format='jS F, Y'}     // 19th February, 1973
{dropdate format='Y-m-d'}       // 1973-02-19
```

## Freeform Pro Template Tags: Basic Usage
```
{freeform:field:dropdate}                          // 98960400
{freeform:field:dropdate dateformat='jS F, Y'}     // 19th February, 1973
{freeform:field:dropdate dateformat='Y-m-d'}       // 1973-02-19
```
