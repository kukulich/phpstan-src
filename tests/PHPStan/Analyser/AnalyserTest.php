<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Parser\DirectParser;
use PHPStan\Rules\AlwaysFailRule;
use PHPStan\Rules\Registry;

class AnalyserTest extends \PHPStan\TestCase
{

	public function dataExclude(): array
	{
		return [
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__],
				[],
				0,
			],
			[
				__DIR__ . '/data/func-call.php',
				[],
				[],
				0,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/*'],
				[],
				0,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/aaa'],
				[],
				1,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/aaa'],
				[
					'#Syntax error#',
				],
				0,
			],
		];
	}

	/**
	 * @dataProvider dataExclude
	 * @param string $filePath
	 * @param string[] $analyseExcludes
	 * @param string[] $ignoreErrors
	 * @param integer $errorsCount
	 */
	public function testExclude(string $filePath, array $analyseExcludes, array $ignoreErrors, int $errorsCount)
	{
		$registry = new Registry();
		$registry->register(new AlwaysFailRule());

		$traverser = new \PhpParser\NodeTraverser();
		$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());

		$broker = $this->createBroker();
		$printer = new \PhpParser\PrettyPrinter\Standard();
		$analyser = new Analyser(
			$broker,
			new DirectParser(new \PhpParser\Parser\Php7(new \PhpParser\Lexer()), $traverser),
			$registry,
			new NodeScopeResolver(
				$broker,
				$printer,
				false,
				false,
				false
			),
			$printer,
			$analyseExcludes,
			$ignoreErrors
		);
		$result = $analyser->analyse([$filePath]);
		$this->assertInternalType('array', $result);
		$this->assertCount($errorsCount, $result);
	}

}
