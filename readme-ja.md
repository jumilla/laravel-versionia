# LARAVEL VERSIONIA

[![Build Status](https://travis-ci.org/jumilla/laravel-versionia.svg)](https://travis-ci.org/jumilla/laravel-versionia)
[![Quality Score](https://img.shields.io/scrutinizer/g/jumilla/laravel-versionia.svg?style=flat)](https://scrutinizer-ci.com/g/jumilla/laravel-versionia)
[![Code Coverage](https://scrutinizer-ci.com/g/jumilla/laravel-versionia/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jumilla/laravel-versionia/)
[![Latest Stable Version](https://poser.pugx.org/jumilla/laravel-versionia/v/stable.svg)](https://packagist.org/packages/jumilla/laravel-versionia)
[![Total Downloads](https://poser.pugx.org/jumilla/laravel-versionia/d/total.svg)](https://packagist.org/packages/jumilla/laravel-versionia)
[![Software License](https://poser.pugx.org/jumilla/laravel-versionia/license.svg)](https://packagist.org/packages/jumilla/laravel-versionia)

Version based database migration system for Laravel 5.

Laravel Versionia（バージョニア） は、バージョンベースのデータベースマイグレーションシステムです。
Laravel 5 と Lumen 5 で使えます。

## コンセプト

Laravel 4 と 5 には、データベース(RDB)スキーマの管理のために「マイグレーション」という機能が標準搭載されています。

マイグレーションはスキーマの作成・変更を時系列で管理していく仕組みです。
データベースの初期データを定義する「シード」の実装のためのPHPクラス、artisanコマンドも提供します。

Versioniaは、標準のマイグレーションをさらに使いやすくします。

- Laravel 5のマイグレーションに、実装者が明示的に定義する「バージョン」の機能を追加します。
- Laravel 5の`Seeder`クラスを名前で識別できるようにし、複数のシードの切り替えを容易にします。
- Laravel 5のアーキテクチャに沿い、サービスプロバイダで提供します。
- マイグレーション、シードのクラスは`app`ディレクトリ下に配置できます。

Laravel 5 アプリケーションやComposerパッケージが機能を提供する仕組みとしてサービスプロバイダが用いられます。
サービスプロバイダでルーティングやイベントリスナーの定義などを行いますが、ここにマイグレーション、シードの定義ができるようになります。

## インストール方法

### [A] Laravel Extension を組み込む (Laravel)

Laravel 5を使用される場合はこちら推奨です。

[Composer](http://getcomposer.org)を使います。

```sh
composer require laravel-plus/extension
```

続いて、`config/app.php`の`providers`に`LaravelPlus\Extension\ServiceProvider::class`を追記します。

```php
    'providers' => [
        ...

        LaravelPlus\Extension\ServiceProvider::class,
    ],
```

詳しくは [Laravel Extension](https://github.com/jumilla/laravel-extension) の説明をお読みください。

### [B] Versionia を組み込む (Laravel)

[Composer](http://getcomposer.org)を使います。

```sh
composer require jumilla/laravel-versionia
```

続いて、`config/app.php`の`providers`に`Jumilla\Versionia\Laravel\ServiceProvider::class`を追記します。

```php
    'providers' => [
        ...

        Jumilla\Versionia\Laravel\ServiceProvider::class,
    ],
```

### [C] Versionia を組み込む (Lumen)

Lumenを使用される場合はこちらをどうぞ。

[Composer](http://getcomposer.org)を使います。

```sh
composer require jumilla/laravel-versionia
```

続いて、`boostrap/app.php`に次のコードを追記します。

```php
$app->register(Jumilla\Versionia\Laravel\ServiceProvider::class);
```

## マイグレーションバージョン定義

今まではマイグレーションクラスのファイル名に命名規則があり、ファイル名に埋め込まれたファイル生成日時によりマイグレーションの順序が決められていました。
Versioniaでは、マイグレーションクラスごとにグループとバージョンを明示的に付与し、`DatabaseServiceProvider`クラスで定義します。

```php
<?php

namespace App\Providers;

use Jumilla\Versionia\Laravel\Support\DatabaseServiceProvider as ServiceProvider;
use App\Database\Migrations;
use App\Database\Seeds;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->migrations('framework', [
            '1.0' => Migrations\Framework_1_0::class,
        ]);

        $this->migrations('app', [
            '1.0' => Migrations\App_1_0::class,
        ]);

        // ... seed definition ...
    }
}
```

### DatabaseServiceProvider の登録

サービスプロバイダを新しく作成した場合は登録してください。
[Laravel Extension](https://github.com/jumilla/laravel-extension) を使用している場合は、既に組み込まれているので追加不要です。

#### Laravel

`app\config.php` に `App\Providers\DatabaseServiceProvider::class` を追記します。

```php
    'providers' => [
        ...
        App\Providers\ConfigServiceProvider::class,
        App\Providers\DatabaseServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        ...
    ],
```

#### Lumen

`bootstrap\app.php` に次のコードを追記します。

```php
$app->register(App\Providers\DatabaseServiceProvider::class);
```

### バージョン番号

Versioniaはバージョン番号の比較に、PHP標準関数の`version_compare()`を用いています。
バージョン番号は、ドット区切りの**文字列**を指定してください。

### マイグレーションクラス

マイグレーションクラスは、Laravel 5標準の`make:migration`で生成されたものがそのまま使えます。

推奨の`app\Database\Migrations`ディレクトリに配置する場合は、`namespace App\Database\Migrations`を追加してください。

### マイグレーション定義

次のコードはマイグレーション定義のサンプルです。

```php
<?php

namespace App\Database\Migrations;

use Jumilla\Versionia\Laravel\Support\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class App_1_0 extends Migration
{
    /**
     * Migrate the database to forward.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->json('properties');
            $table->timestamps();
        });
    }

    /**
     * Migrate the database to backword.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
```

## シード定義

次のサンプルコードでは、シード `test`, `staging`, `production` を定義しています。
`seeds()`メソッドの第2引数はデフォルトシードの指定で、`test`を指定しています。

```php
<?php

namespace App\Providers;

use Jumilla\Versionia\Laravel\Support\DatabaseServiceProvider as ServiceProvider;
use App\Database\Migrations;
use App\Database\Seeds;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    	//... migration definition ...

        $this->seeds([
            'test' => Seeds\Test::class,
            'staging' => Seeds\Staging::class,
            'production' => Seeds\Production::class,
        ], 'test');
    }
}
```

シードクラスは次のように記述します。

```php
<?php

namespace App\Database\Seeds;

use Jumilla\Versionia\Laravel\Support\Seeder;
use Carbon\Carbon;

class Staging extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        app('db')->table('posts')->truncate();

        app('db')->table('posts')->insert([
            'title' => 'Sample post',
            'content' => 'Hello laravel world.',
            'properties' => json_encode([
                'author' => 'Seeder',
            ], JSON_PRETTY_PRINT),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
```

## コマンド

### `database:status`

マイグレーション、シードの定義とインストール状態を表示します。

```sh
php artisan database:status
```

### `database:upgrade`

すべてのグループのマイグレーションの`up()`を実行し、最新バージョンにします。

```sh
php artisan database:upgrade
```

マイグレーション後にシードを実行させることもできます。

```sh
php artisan database:upgrade --seed <シード>
```

### `database:clean`

すべてのグループのマイグレーションの`down()`を実行し、クリーン状態に戻します。

```sh
php artisan database:clean
```

### `database:refresh`

すべてのグループのマイグレーションをやり直します。

`database:clean`と`database:upgrade`を実行した結果と同じです。

```sh
php artisan database:refresh
```

マイグレーション後にシードを実行させることもできます。

```sh
php artisan database:refresh --seed <シード>
```

### `database:rollback`

指定グループのバージョンをひとつ戻します。

```sh
php artisan database:rollback <グループ>
```

`--all`オプションを付けると、指定グループのすべてのバージョンを削除します。

```sh
php artisan database:rollback <グループ> --all
```

### `database:again`

指定グループの最新バージョンを再作成します。

`database:rollback <グループ>`と`database:upgrade`を実行したときと同じ効果があります。

```sh
php artisan database:again <グループ>
```

マイグレーション後にシードを実行させることもできます。

```sh
php artisan database:again <グループ> --seed <シード>
```

### `database:seed`

指定のシードを実行します。

```sh
php artisan database:seed <シード>
```

`<シード>`を省略した場合、デフォルトのシードを実行します。

```sh
php artisan database:seed
```

## 著作権

古川 文生 / Fumio Furukawa (fumio@jumilla.me)

## ライセンス

MIT
