<?php
/*
 * This file is a part of the Enginiro project.
 *
 * Copyright (c) Enginiro project. For the full copyright and licensing information, please view the LICENSE.adoc file
 * that was distributed with this source code.
 */

declare(strict_types=1);

namespace Enginiro\AssetBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EnginiroAssetBundle extends Bundle {
	public function getPath(): string {
		return \dirname(__DIR__);
	}
}