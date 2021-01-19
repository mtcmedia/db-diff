# Database Diff Checker

This tool is a database schema comparison tool within Laravel. 
It is meant to be used to compare schemas for tables or table columns between 
two similar databases.

Tool is primarily tested on MySql however should work on other schemas as well.

# Installation & Usage
Install package using composer
```
composer require mtcmedia/db-diff
```

Laravel auto-registers module and its commands so no other installation steps required.

Package offers following commands

```
php artisan db-diff:list-tables 

php artisan db-diff:list-columns 
```

The list tables command will compare table names only and list any missing tables 
on your local (default) database.

The list columns command will compare all columns in tables and list missing columns 
(or full tables with all columns) in both directions

Database selection uses `config/database.php` to list connections that can be used for comparison.

## Contributing

Please see [CONTRIBUTING](contributing.md) for details.

### Security

If you discover any security-related issues, please email [opensource@mtcmedia.co.uk](mailto:opensource@mtcmedia.co.uk) instead of using the issue tracker.

## License

The package is provided under MIT License. Please see [License File](license.md) for more information.
