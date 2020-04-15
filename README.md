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
