<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'my_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../../Model/Simple'
                ),
            ),

            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    'DoctrineTest\Test\Assets\Model\Simple' => 'my_annotation_driver'
                )
            )
        )
    ),

    'router' => [],
    'view_manager' => [],
    'zenddevelopertools' => []
);