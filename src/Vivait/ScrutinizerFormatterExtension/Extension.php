<?php

namespace Vivait\ScrutinizerFormatterExtension;

use PhpSpec\Extension\ExtensionInterface;
use PhpSpec\Formatter\Presenter\StringPresenter;
use PhpSpec\ServiceContainer;
use Vivait\ScrutinizerFormatterExtension\Loader\ResourceLoader;

class Extension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ServiceContainer $container)
    {
        $this->addFormatter(
          $container,
          'scrutinizer',
          'Vivait\ScrutinizerFormatterExtension\Formatter\ScrutinizerFormatter'
        );

        $this->overrideLoader($container);
    }

    protected function overrideLoader(ServiceContainer $container)
    {
        $container->setShared(
          'loader.resource_loader',
          function (ServiceContainer $c) {
              return new Loader\ResourceLoader($c->get('locator.resource_manager'));
          }
        );
    }

    /**
     * Add a formatter to the service container
     *
     * @param ServiceContainer $container
     * @param string $name
     * @param string $class
     */
    protected function addFormatter(ServiceContainer $container, $name, $class)
    {
        $container->set(
          'formatter.formatters.' . $name,
          function (ServiceContainer $c) use ($class) {
              $c->set('formatter.presenter', new StringPresenter($c->get('formatter.presenter.differ')));

              /** @var ServiceContainer $c */
              return new $class(
                $c->get('formatter.presenter'),
                $c->get('console.io'),
                $c->get('event_dispatcher.listeners.stats')
              );
          }
        );
    }
}
