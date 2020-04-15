UpplyFileManagerBundle
======================
Symfony bundle to easily handle file storage in a standard way


Installation
------------
With composer:

Add the private repository url in your project `composer.json`:
```json
...
"repositories": [
    ...
    { "type": "vcs", "url": "https://github.com/SBLogX/UpplyFileManagerBundle" },
    ...
  ],
...
"require": {
    ...
    "upply/file-manager-bundle": dev-master,
    ...
}
```

Then run composer update commande:
```shell
$ composer update
```

Configuration
-------------
Configure the `upply_file_manager.yaml` in your `config/package` directory:
```yaml
upply_file_manager:
    blob_storage_url: '%env(BLOB_STORAGE_URL)%'
    storage_dirnames:
        client:
            name: 'client-files'

knp_gaufrette:
    adapters:
        default:
            local:
                directory: '%kernel.project_dir%/fileshare'
        azure:
            azure_blob_storage:
                blob_proxy_factory_id: azure_blob_proxy_factory
                container_name: '%env(ENV)%'
                create_container: false

    stream_wrapper: ~
```

How to use
----------

Define a relative path that will be use to store/retrieve/delete your file from a namespace (here: 'client')
```php
$relativeFilePath = sprintf(
    'company-%d/order-documents/order-%s/%s',
    $this->getUser()->getCompanyId(),
    $order->getReference(),
    $documentReference
);

$fileManager->has('client', $relativeFilePath);
$fileManager->write('client', $relativeFilePath, file_get_contents($document->getRealPath()));
$fileManager->get('client', $relativeFilePath);
$fileManager->delete('client', $relativeFilePath);
```

In a controller, to return the file:
```php
return new BinaryFileResponse($fileManager->getFilePath('client', $relativeFilePath, true));
```
