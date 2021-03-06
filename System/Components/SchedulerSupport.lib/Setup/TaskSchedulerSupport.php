<?php
class Setup_TaskSchedulerSupport
	extends _Setup
	implements
		Setup_ForDatabaseTables,
		Setup_ForDatabaseTableReferences
{
	public function runDatabaseTablesSetup() {
		$this->setupInDatabase(
				'jobsTable',
				'jobSchedulesTable'
			);
	}

	public function runDatabaseTableReferencesSetup() {
		$this->setupInDatabase(
				'jobsReferences',
				'jobSchedulesReferences'
			);
	}
}
?>