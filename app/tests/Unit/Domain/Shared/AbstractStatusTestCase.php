<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shared;

use App\Domain\Shared\StatusTransitions;
use PHPUnit\Framework\TestCase;

/**
 * @template T of \BackedEnum&StatusTransitions
 */
abstract class AbstractStatusTestCase extends TestCase
{
    /** @return list<T> */
    abstract protected static function statusCases(): array;

    /** @return T */
    abstract protected static function fromValue(int|string $v): mixed;

    /** @return array<int|string, list<int|string>> */
    abstract protected static function expected(): array;

    /** @return array<string> */
    abstract protected static function terminalStatuses(): array;

    /** @return T */
    abstract protected static function rootStatus(): mixed;

    public function testEveryStatusHasDeclaredExpectations(): void
    {
        $declared = array_keys(static::expected());
        $actual = array_map(static fn (\BackedEnum&StatusTransitions $s): int|string => $s->value, static::statusCases());

        $diff = [...array_diff($actual, $declared), ...array_diff($declared, $actual)];
        $this->assertEmpty($diff, sprintf('Do not declared status(es): %s', implode(', ', $diff)));
    }

    public function testIfEachDeclaredTransitionIsAllowed(): void
    {
        $declared = static::expected();
        $isAllowSetTo = [];
        foreach ($declared as $parent => $child) {
            if (empty($child)) {
                continue;
            }
            $status = static::fromValue($parent);
            $isAllowSetTo[$parent] = array_all($child, static fn (int|string $children) => $status->canSetTo(static::fromValue($children)));
        }

        $brokenStatus = array_find_key($isAllowSetTo, fn (bool $isAllowed) => !$isAllowed);
        $this->assertEmpty($brokenStatus, sprintf("Some of branches of the `%s` status can't be set to", $brokenStatus));
    }

    public function testIfNotDeclaredTransitionExists(): void
    {
        $declared = static::expected();
        $actual = static::statusCases();
        $actualTransitions = [];
        $notDeclaredTransitions = [];
        foreach ($actual as $status) {
            $actualTransitions[$status->value] = array_filter(array_map(static fn (\BackedEnum&StatusTransitions $testStatus) => ($status->canSetTo($testStatus)) ? $testStatus->value : false, $actual));
            $notDeclaredTransitions[$status->value] = array_diff($actualTransitions[$status->value], $declared[$status->value]);
        }
        $notDeclaredTransitions = array_filter($notDeclaredTransitions);
        $this->assertEmpty($notDeclaredTransitions, sprintf('The `%s` status(es) do(es) declared full', implode(', ', array_keys($notDeclaredTransitions))));
    }

    public function testTerminalStatusesAllowNothing(): void
    {
        $terminatedList = static::terminalStatuses();
        $allStatuses = static::statusCases();
        $isAllowSetToAny = [];
        foreach ($terminatedList as $terminated) {
            $terminatedStatus = static::fromValue($terminated);
            $isAllowSetToAny[$terminated] = array_filter(array_map(static fn (\BackedEnum&StatusTransitions $status) => ($terminatedStatus->canSetTo($status)) ? $status->value : false, $allStatuses));
        }

        $brokenStatus = array_find_key($isAllowSetToAny, fn (array $isAllowed) => !empty($isAllowed));

        $this->assertEmpty($brokenStatus, sprintf('The `%s` status that was marked as terminal is allow to set `%s` status(es)', $brokenStatus, implode(', ', $isAllowSetToAny[$brokenStatus] ?? [])));
    }

    public function testIfNotTerminalStatusEmpty(): void
    {
        $terminatedList = static::terminalStatuses();
        $allStatuses = static::statusCases();
        $missTerminalDefinition = [];
        foreach ($allStatuses as $status) {
            $isAllowSetToAny = array_any($allStatuses, static fn (\BackedEnum&StatusTransitions $targetStatus) => $status->canSetTo($targetStatus));
            if (!$isAllowSetToAny && !in_array($status->value, $terminatedList)) {
                $missTerminalDefinition[] = $status->value;
            }
        }
        $this->assertEmpty($missTerminalDefinition, sprintf('The `%s` status(es) not marked as terminal but with empty rules', implode(', ', $missTerminalDefinition)));
    }

    public function testNoSelfTransition(): void
    {
        $canSetSelf = array_map(static fn (\BackedEnum&StatusTransitions $s): bool|int|string => $s->canSetTo($s) ? $s->value : false, static::statusCases());
        $this->assertEmpty(array_filter($canSetSelf), sprintf('Status(es) allow self transition: %s', implode(', ', $canSetSelf)));
    }

    public function testEveryStatusIsReachableFromRoot(): void
    {
        $rootStatus = static::rootStatus();
        $reachableStatuses = array_keys($this->getPathBranch(visited: [], status: $rootStatus->value));
        $actual = array_map(static fn (\BackedEnum&StatusTransitions $s): int|string => $s->value, static::statusCases());

        sort($reachableStatuses);
        sort($actual);

        $diff = array_diff($actual, $reachableStatuses);
        $this->assertEmpty($diff, sprintf("Can't reache to status(es): %s from the draft", implode(', ', $diff)));
    }

    /**
     * @param array<int|string> $visited
     *
     * @return array<int|string>
     */
    private function getPathBranch(array $visited, int|string $status): array
    {
        $declared = static::expected();
        $visited[$status] = $status;
        $paths = $declared[$status];
        foreach ($paths as $path) {
            if (isset($visited[$path])) {
                continue;
            }
            $visited = $this->getPathBranch($visited, $path);
        }

        return $visited;
    }
}
