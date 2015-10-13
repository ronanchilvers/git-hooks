# git-hooks

Simple git hooks for php projects. Currently only does a lint of all files in the commit.

## Install

Clone this repository somewhere and do a

```bash
composer install
```

Then change to the project that you want to install the hook in and do

```bash
php /path/to/git-hooks/bin/git-hooks install pre-commit

```
