# HTTP Server Request Change Log

## 1.1.0 - 2023.05.05

### Changed

- [#5](https://github.com/httpsoft/http-server-request/pull/5) Rises `httpsoft/http-message` package version to `^1.1`.

## 1.0.6 - 2023.05.05

### Fixed

- [#4](https://github.com/httpsoft/http-server-request/pull/4) Fixes parsing host from `HTTP_HOST` header to `HttpSoft\ServerRequest\SapiNormalizer`.

## 1.0.5 - 2021.07.20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [Fixes](https://github.com/httpsoft/http-server-request/commit/6552246f34d767a33bb23b4348b8560d41e15136) `HttpSoft\ServerRequest\SapiNormalizer::normalizeHeaders()` method.
- [#1](https://github.com/httpsoft/http-server-request/pull/1) adds test cases for code coverage and updates of workflow actions.

## 1.0.4 - 2020.12.12

### Added

- Nothing.

### Changed

- Updates development dependencies.
- Updates GitHub actions.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.3 - 2020.09.06

### Added

- Adds implementations declaration to the `composer.json`.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.2 - 2020.08.28

### Added

- Adds support OS Windows to build github action.
- Adds files to `.github` folder (ISSUE_TEMPLATE, PULL_REQUEST_TEMPLATE.md, CODE_OF_CONDUCT.md, SECURITY.md).

### Changed

- Moves static analysis and checking of the code standard to an independent github action.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - 2020.08.25

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Moves Psalm issue handlers from psalm.xml to docBlock to appropriate methods.

## 1.0.0 - 2020.08.23

- Initial stable release.
