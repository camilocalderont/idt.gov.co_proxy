# Block Token

The Block Token module allows users to specify the blocks that will have the
tokens generated for use within text formats using Token Filter module.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/block_token).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/block_token).


## Table of contents

- Requirements
- Installation
- Configuration
- Maintainers


## Requirements

This module requires the following modules:

- [Token](https://drupal.org/project/token)
- [Token Filter](https://drupal.org/project/token_filter)


## Installation

Install as you would normally install a contributed Drupal module. See:
[Installing Drupal Modules](https://drupal.org/documentation/install/modules-themes/modules-8)
for further information.


## Configuration

1. Enable "Replace Tokens" filter for specific text formats available on
  `Administration » Configuration » Content authoring » Text Formats`.
2. Edit the blocks: check the "Create the token for this block" checkbox for the
  block on edit screen.
3. Put the token of enabled block.
  E.g. [block_token:system:navigation], to the content field
  formatted with the text format with token replacement.


## Maintainers

- Oleg Dyachenko - [skullhole](https://www.drupal.org/u/gnikolovski)
- Ivan Trokhanenko - [i-trokhanenko](https://www.drupal.org/u/i-trokhanenko)
