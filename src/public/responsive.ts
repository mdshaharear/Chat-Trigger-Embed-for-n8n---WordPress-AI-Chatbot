export function applyResponsiveVars(root: HTMLElement, variables: Record<string, string>): void {
	Object.entries(variables).forEach(([key, value]) => {
		root.style.setProperty(key, value);
	});
}
