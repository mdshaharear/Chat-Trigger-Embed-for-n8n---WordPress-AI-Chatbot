export type RuntimeReport = {
	reportSchemaVersion: string;
	pluginVersion: string;
	databaseVersion: string;
	wordpressVersion: string;
	phpVersion: string;
	theme: string;
};

export function serializeRuntimeReport(report: RuntimeReport): string {
	return JSON.stringify(report, null, 2);
}

export function serializeRuntimeReportText(report: RuntimeReport): string {
	return [
		`Report Schema Version: ${report.reportSchemaVersion}`,
		`Plugin Version: ${report.pluginVersion}`,
		`Database Version: ${report.databaseVersion}`,
		`WordPress Version: ${report.wordpressVersion}`,
		`PHP Version: ${report.phpVersion}`,
		`Theme: ${report.theme}`
	].join('\n');
}
