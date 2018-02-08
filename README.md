### Overview
Module `LizardMedia_ProductAttachment` adds possibility to add attachment for products.

#### Prerequisites
In case of uploading bigger files may be necessary adjusting some configuration with higher values:
* nginx - `client_max_body_size`
* php - `memory_limit` && `upload_max_filesize` && `post_max_size`

#### Features

* add and manage attachments to product via adminhtml form
* display attachments on product view page
* send attachments for products bought
* display attachments in customer account bookmark