[![GitHub license](https://img.shields.io/github/license/Fanmade/laravel-service-binding)](https://github.com/Fanmade/laravel-service-binding/blob/main/LICENSE)
[![Code Coverage](https://codecov.io/gh/fanmade/laravel-service-binding/branch/main/graph/badge.svg)](https://codecov.io/gh/fanmade/laravel-service-binding)
# EARLY WORK IN PROGRESS, NOT USABLE YET

# Laravel Service Binding

Laravel does provide all necessary tools to allow using service or repository binding.
Just bind it in the service provider and you're good to go.
```
public function register() 
   { 
       $this->app->bind(FooRepositoryInterface::class, EloquentFooRepository::class);
       $this->app->bind(FooSearchServiceInterface::class, EloquentFooSearchService::class);
   }
```
Now if you created an `ElasticSearchFooSearchService` and you did everything properly, you only have to change the binding and everything should work fine.
But there are several reasons why you might use different services on different systems and that can get messy quickly. You also can't switch between different repositories without altering code :(
Maybe you start using `if`/`else` in the service provider now.
```
public function register() 
   { 
       $this->app->bind(FooRepositoryInterface::class, EloquentFooRepository::class);
       if (env('ELASTICSEARCH_FOO_SERVICE', 'default')) {
          $this->app->bind(FooSearchServiceInterface::class, ElasticSearchFooSearchService::class);
       } else {
          $this->app->bind(FooSearchServiceInterface::class, EloquentFooSearchService::class);
       }
   }
```
Or you switch to Symfony where that's not a problem because you only have to update your configuration files.

This package here does try to provide a solution for Laravel applications.
