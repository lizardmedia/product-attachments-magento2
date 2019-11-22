# Magento 2 Product Attachments

Module `LizardMedia_ProductAttachment` adds possibility to add attachment for products.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

* Magento 2.2
* PHP 7.1

### Installing

#### Download the module

##### Using composer (suggested)

Add the repository to your composer.json
```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/lizardmedia/product-attachments-magento2"
    }
]
```
and run

```
composer require lizardmedia/product-attachments-magento2
```

##### Downloading ZIP

Download a ZIP version of the module and unpack it into your project into
```
app/code/LizardMedia/ProductAttachment
```

#### Install the module

Run this command
```
bin/magento module:enable LizardMedia_ProductAttachment
bin/magento setup:upgrade
```

## Usage

#### Admin panel

* add and manage attachments to product via product edit form

![admin-product-form](https://user-images.githubusercontent.com/20238194/47045326-d7c14c80-d192-11e8-91fb-1d8585b1ebe0.png)


#### Frontend
* display attachments on product view page
* display attachments in customer account bookmark

## For developers

In case of uploading bigger files may be necessary adjusting some configuration with higher values:
* nginx - `client_max_body_size`
* php - `memory_limit` && `upload_max_filesize` && `post_max_size`

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/lizardmedia/varnish-warmer-magento2/tags).

## Authors

* **Bartosz Kubicki** - *Initial work* - [Lizard Media](https://github.com/bartoszkubicki)

See also the list of [contributors](https://github.com/lizardmedia/product-attachments-magento2/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## To do

* allow attachment variations depending on store view
* release new version fully compatible with Magento 2.3+ with tests coverage