# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Added - for new features.
Changed - for changes in existing functionality.
Deprecated - for soon-to-be removed features.
Removed - for now removed features.
Fixed - for any bug fixes.
Security - in case of vulnerabilities.

## [Unreleased] - XXXX-XX-XX
### Fixed
- Organisations and groups search order
- Memory usage accumulation during conversion (xls, ods, slk, csv)

## [1.0.13] - 2019-01-31
### Added
- Migrated data statistics command (php artisan data:statistics)
- File input for resources with type Automatic upload

## [1.0.12] - 2018-12-11
### Added
- Scroll in dropdown filters
- Command for re-download broken resources

### Fixed
- Newsletter on post
- Resource type API automatic update
- Resource file type pdf upload
- Resource type API update interval check
- Personal data check on resource edit
- Data set links in Data page

## [1.0.11] - 2018-12-03
### Fixed
- Fix migration undefined index

## [1.0.10] - 2018-12-03
### Fixed
- Resource versions order
- Resource preview for non-authenticated users

## [1.0.9] - 2018-12-03
### Fixed
- Remove comment button from documents view
- Resource preview
- Tool time filter in history page
- Escape XML resource special characters
- Tool DBSM edit and file check
- Undefined variable in migration

## [1.0.8] - 2018-11-26
### Added
- Tool file data preview
- Tool multiple databases support
- Tool history pagination on top

### Changed
- Tool devide database and file connections
- Refactor tool code

### Fixed
- Fix resource query script parameters format
- Resource preview for all formats
- Tool database preview
- Tool history pagination clears filters
- Tool query filter
- Tool driver filter
- Tool type filter

## [1.0.7] - 2018-11-22
### Added
- Automatic upload resource type
- Api updateResourceData format parameter

### Fixed
- Automatic newsletters and tool resources send timing
- Tool file folder syncronisation on automatic restart

## [1.0.6] - 2018-11-20
### Changed
- Elastic search index clean command

## [1.0.5] - 2018-11-20
### Added
- Elastic search index clean command

### Fixed
- Resource download needs authentication
- Main categories file preview in admin panel
- Login history bug
- Dataset filtering bug

### Changed
- Base categories names in insert migration

## [1.0.4] - 2018-11-15
### Fixed
- Personal information check to work for 10 digit chunks only
- Send newsletters on post
- Edit API information for resources type API
- Tool history
- Tool mail notifications and errors
- Tool cron resource updates

### Added
- Мodules code in API listModules() response
- Module "Modules" in rights list

## [1.0.3] - 2018-11-14
### Fixed
- uri in listDataGroups() and listDataOrganisations() responses
- Tool install script container destroy order for windows
- Tool graylog host
- Tool file exists check

## [1.0.2] - 2018-11-14
### Added
- Resource queries tool in admin edit pages section

### Changed
- Remove 'help_section' parameter from API SectionController::addSection() and editSection()
- Remove 'help_page' parameter from API SectionController::addPage() and editPage()
- Drop 'help_section' column from 'sections' table
- Drop 'help_page' column from 'pages' table

### Fixed
- Hide not active sections, subsections and pages from Help guide
- Add missing right checks API functions
- Admin documents edit
- Tool dockerfile api url
- Tool database connection for localhosts
- Tool persistent database volume
- Tool cron resource updates
- Tool history statuses
- Tool file not found error

## [1.0.1] - 2018-11-05
### Added
- Statistics and analytics link in admin navigation bar
- Tool daily database external backups

### Changed
- Images and documents to database instead of files
- Add resources info in RSS feed

### Fixed
- Forum discussions for public data section
- Admin inactive sections preview
- Admin inactive help sections creation
- Api missing routes error
- Api groups and organisations unique id error message
- Api groups and organisations image upload
- Main category adding
- Main category without image showing
- User rights on resources in user panel
- Microsoft Edge help icon fix
- API conversions from json format

## [1.0.0] - 2018-10-31
### Added
- Resource update equal data in previous version check
- Tool mail notifications
- Resource queries tool in admin add pages section
- Send query button functionality for the tool
- Edit saved query functionality for the tool
- Send newsletters
- Tool installation and docker files
- Automated api resource upload
- Api getResourceView function

### Changed
- Search engine sqlite indexes to mysql
- Add admin check for editing and adding organisations
- Tool file uploads with docker volume

### Remove
- Cron job for sqlite index updates

### Fixed
- Help page stays open after being selected
- Images uploaded from admin panel show for all users
- If help page is inactive you can go to edit from the help sidebar
- Timezone to Europe/Sofia
- Resource from docx
- Resource visualisation ordering

## [0.6.5] - 2018-10-24
### Added
- Users can add datasets to groups from a public dataset view
- More specific error message for resources upload

### Changed
- Allow only svg in admin category upload

### Fixed
- Modals not opening
- Category list icon data encoding
- Dataset buttons in public section
- Resource redirect after delete
- Search indexes permissions
- Update datasets version
- Subsection not deleting
- Connection to APIs for resource data
- On user login set locale from user settings
- listUsers() response in xml and without api key
- Cyrillic letters in downloaded json
- Dataset filters in public section

## [0.6.4] - 2018-10-19
### Added
- Forum discussions to public data and documents sections
- Cron task to initiate search indexes update every 5 minutes

### Changed
- Help stays open until closed
- Help no longer transparent
- Help close icon changed
- Optimised tnt search indexes refresh command
- Remove searchable models automatic index sync on create and update

### Fixed
- Editing active and inactive help sections and pages
- Scroll shows in help container
- Redirect after dataset creation
- Hovering main categories
- Show help section icon in footer on mobile view
- Filter resource download formats

## [0.6.3] - 2018-10-18
### Added
- TNT search indexes refresh command
- Visualizations pages

### Changed
- Translations table text column length
- Group datasets delete button changed to remove
- Group datasets edit button removed

### Fixed
- WYSIWYG editor code view, full screen mode
- Missing conversion methods
- Unclosed span tag
- Search pagination
- HTML error when creating dataset form API
- Bug in showing inactive news to logged users
- Visual glitches in IE and mobile version
- Bugs in showing system information about records
- Dubious column names in documents and organisations
- Resource download

## [0.6.2] - 2018-10-17
### Fixed
- Organisations search
- Delete own account
- Account creator

## [0.6.1] - 2018-10-16
### Added
- Api removeDatasetFromGroup parameter group_id now is a array of ids
- Add api connnection for files, soft deletes and update history (Tool)
- Version link (Tool)
- History pagination (Tool)
- Visual impovements

### Fixed
- Help sections optimisation
- Datasets and resources version bug
- Resources and datasets refactoring and optimisation
- Edit dataset can not deselect groups and organisation
- Dataset edit groups and organisation inputs
- Show inactive subsections and sections in admin area
- Invite bug
- Sticky footer

## [0.6.0] - 2018-10-08
### Added
- Change log
- Tool history page
- RSS feed for news
- RSS feed for datasets
- Insert base terms of use
- Google analytics integration
- Filter for personal information
- Help functionality for all pages
- Required sections and static pages
- RSS feed for organisation datasets
- Map old license ids with the new terms of use

### Fixed
- Refactored and optimised tests
- Activation link for deactivated users
- Admin subsections listing

## [0.5.1] - 2018-10-03
### Fixed
- Api sorting improvement
- Document upload and download fixes
- Various code refactoring
- Filter improvements
- Data migration fixes

### Removed
- Duplicate search functionality from api methods

## [0.5.0] - 2018-10-01
### Added
- Inital tool and tool design
- Help pages and help pages controls to admin panel
- Social networks sharing
- Public datasets, organisations and groups
- User experience upgrades through additional controls
- Various visual improvements

### Fixed
- Addition of missing base roles to migration
- Data migration fixes
- Search engine fixes

[Unreleased]: https://github.com/governmentbg/data-gov-bg/compare/vUnreleased...HEAD
[1.0.13]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.12...v1.0.13
[1.0.12]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.11...v1.0.12
[1.0.11]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.10...v1.0.11
[1.0.10]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.9...v1.0.10
[1.0.9]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.8...v1.0.9
[1.0.8]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.7...v1.0.8
[1.0.7]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.6...v1.0.7
[1.0.6]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.5...v1.0.6
[1.0.5]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.4...v1.0.5
[1.0.4]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/governmentbg/data-gov-bg/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/governmentbg/data-gov-bg/compare/v0.6.5...v1.0.0
[0.6.5]: https://github.com/governmentbg/data-gov-bg/compare/v0.6.4...v0.6.5
[0.6.4]: https://github.com/governmentbg/data-gov-bg/compare/v0.6.3...v0.6.4
[0.6.3]: https://github.com/governmentbg/data-gov-bg/compare/v0.6.2...v0.6.3
[0.6.2]: https://github.com/governmentbg/data-gov-bg/compare/v0.6.1...v0.6.2
[0.6.1]: https://github.com/governmentbg/data-gov-bg/compare/v0.6.0...v0.6.1
[0.6.0]: https://github.com/governmentbg/data-gov-bg/compare/v0.5.1...v0.6.0
[0.5.1]: https://github.com/governmentbg/data-gov-bg/compare/v0.5.0...v0.5.1
[0.5.0]: https://github.com/governmentbg/data-gov-bg/compare/v0.4.1...v0.5.0
