(function () {
	'use strict';

	function q(selector, scope) {
		return (scope || document).querySelector(selector);
	}

	function qa(selector, scope) {
		return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
	}

	function copyOrigin() {
		var button = q('[data-cten-copy-origin]');
		var origin = q('#cten-origin');
		if (!button || !origin || !navigator.clipboard) {
			return;
		}

		button.addEventListener('click', function () {
			navigator.clipboard.writeText(origin.textContent || '').then(function () {
				button.textContent = 'Copied';
				window.setTimeout(function () {
					button.textContent = 'Copy Origin';
				}, 1500);
			});
		});
	}

	function mirrorPreview() {
		var preview = q('.cten-preview');
		if (!preview) {
			return;
		}

		qa('[data-preview-size]', preview).forEach(function (button) {
			button.addEventListener('click', function () {
				qa('[data-preview-size]', preview).forEach(function (other) {
					other.classList.toggle('is-active', other === button);
				});
				preview.setAttribute('data-preview-size', button.getAttribute('data-preview-size'));
			});
		});

		qa('.cten-form input, .cten-form textarea, .cten-form select').forEach(function (field) {
			field.addEventListener('input', update);
			field.addEventListener('change', update);
		});

		var validateButton = q('[data-cten-validate-webhook]');
		var diagnostic = q('#cten-webhook-diagnostic');
		var webhookInput = q('#cten_webhook_url');
		if (validateButton && diagnostic && webhookInput) {
			function updateDiagnostic() {
				var value = (webhookInput.value || '').trim();
				var message = '';
				if (!value) {
					message = 'Paste the production Chat Trigger URL from an active workflow.';
				} else if (value.toLowerCase().indexOf('webhook-test') !== -1) {
					message = 'This looks like a test webhook. Use the production Chat Trigger URL instead.';
				} else {
					try {
						var parsed = new URL(value);
						if (parsed.protocol !== 'https:' && parsed.hostname !== 'localhost' && parsed.hostname !== '127.0.0.1') {
							message = 'HTTPS is recommended for production sites.';
						} else {
							message = 'Production URL looks valid. Final verification still requires a real chat message.';
						}
					} catch (error) {
						message = 'The URL structure is not valid. Copy the production URL directly from n8n.';
					}
				}
				diagnostic.textContent = message;
			}

			validateButton.addEventListener('click', updateDiagnostic);
			webhookInput.addEventListener('input', updateDiagnostic);
			updateDiagnostic();
		}

		function update() {
			var title = q('input[name="bot_name"]');
			var subtitle = q('input[name="bot_subtitle"]');
			var welcome = q('textarea[name="welcome_message"]');
			var input = q('input[name="input_placeholder"]');
			var privacy = q('textarea[name="follow_up_privacy_text"]');
			var titleNode = q('.cten-preview__title', preview);
			var subtitleNode = q('.cten-preview__subtitle', preview);
			var botMessage = q('.cten-preview__message--bot', preview);
			var inputNode = q('.cten-preview__input span', preview);
			var privacyNode = q('.cten-preview__footer p', preview);
			if (title && titleNode) {
				titleNode.textContent = title.value;
			}
			if (subtitle && subtitleNode) {
				subtitleNode.textContent = subtitle.value;
			}
			if (welcome && botMessage) {
				botMessage.textContent = welcome.value;
			}
			if (input && inputNode) {
				inputNode.textContent = input.value;
			}
			if (privacy && privacyNode) {
				privacyNode.textContent = privacy.value;
			}
		}
		update();
	}

	function copyDiagnostics() {
		var button = q('[data-cten-copy-diagnostics]');
		if (!button || !navigator.clipboard) {
			return;
		}

		button.addEventListener('click', function () {
			var report = qa('.cten-stats li').map(function (item) {
				return (item.textContent || '').replace(/\s+/g, ' ').trim();
			}).join('\n');
			navigator.clipboard.writeText(report).then(function () {
				button.textContent = 'Copied';
				window.setTimeout(function () {
					button.textContent = 'Copy Diagnostics Report';
				}, 1500);
			});
		});
	}

	function warnUnsavedChanges() {
		var forms = qa('.cten-form');
		var dirty = false;
		forms.forEach(function (form) {
			form.addEventListener('input', function () {
				dirty = true;
			});
			form.addEventListener('submit', function () {
				dirty = false;
			});
		});

		window.addEventListener('beforeunload', function (event) {
			if (!dirty) {
				return;
			}
			event.preventDefault();
			event.returnValue = '';
		});
	}

	function profileSearch() {
		var input = q('[data-cten-profile-search]');
		if (!input) {
			return;
		}

		input.addEventListener('input', function () {
			var needle = (input.value || '').toLowerCase();
			qa('[data-cten-profile-card]').forEach(function (card) {
				card.hidden = needle && (card.textContent || '').toLowerCase().indexOf(needle) === -1;
			});
		});
	}

	async function runtimeLabRequest(path, body) {
		if (!window.ctenRuntimeLab) {
			throw new Error('Runtime Lab is unavailable');
		}
		var response = await fetch(window.ctenRuntimeLab.restUrl.replace(/\/$/, '') + path, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.ctenRuntimeLab.restNonce
			},
			body: JSON.stringify(body || {})
		});
		return await response.json();
	}

	function stringifyReport(report) {
		return JSON.stringify(report, null, 2);
	}

	function wireRuntimeLab() {
		if (!window.ctenRuntimeLab) {
			return;
		}

		qa('[data-cten-run-tests]').forEach(function (button) {
			button.addEventListener('click', async function () {
				button.disabled = true;
				try {
					var data = await runtimeLabRequest('/runtime-lab/run', { category: button.getAttribute('data-cten-category') || 'all' });
					var output = q('[data-cten-report]');
					if (output) {
						output.textContent = stringifyReport(data);
					}
				} catch (error) {
					console.error(error);
				} finally {
					button.disabled = false;
				}
			});
		});

		var mockButton = q('[data-cten-run-mock]');
		if (mockButton) {
			mockButton.addEventListener('click', async function () {
				mockButton.disabled = true;
				try {
					var scenario = (q('[data-cten-mock-scenario]') || {}).value || 'success';
					var data = await runtimeLabRequest('/runtime-lab/mock', { scenario: scenario });
					var output = q('[data-cten-mock-output]');
					if (output) {
						output.textContent = stringifyReport(data);
					}
				} catch (error) {
					console.error(error);
				} finally {
					mockButton.disabled = false;
				}
			});
		}

		var liveButton = q('[data-cten-run-live-test]');
		if (liveButton) {
			liveButton.addEventListener('click', async function () {
				var confirmBox = q('[data-cten-live-confirm]');
				if (!confirmBox || !confirmBox.checked) {
					alert('Please confirm the live n8n test first.');
					return;
				}
				liveButton.disabled = true;
				try {
					var data = await runtimeLabRequest('/runtime-lab/live-test', { confirm: true });
					var output = q('[data-cten-live-output]');
					if (output) {
						output.textContent = stringifyReport(data);
					}
				} catch (error) {
					console.error(error);
				} finally {
					liveButton.disabled = false;
				}
			});
		}

		var copyReport = q('[data-cten-copy-report]');
		if (copyReport) {
			copyReport.addEventListener('click', async function () {
				var report = q('[data-cten-report]');
				if (navigator.clipboard && report) {
					await navigator.clipboard.writeText(report.textContent || '');
				}
			});
		}

		var downloadMockReport = q('[data-cten-download-mock-report]');
		if (downloadMockReport) {
			downloadMockReport.addEventListener('click', function () {
				var output = q('[data-cten-mock-output]');
				var blob = new Blob([output ? output.textContent || '' : ''], { type: 'text/plain;charset=utf-8' });
				var link = document.createElement('a');
				link.href = URL.createObjectURL(blob);
				link.download = 'cten-mock-test-report.txt';
				link.click();
				window.setTimeout(function () {
					URL.revokeObjectURL(link.href);
				}, 1000);
			});
		}

		qa('[data-cten-download-report]').forEach(function (button) {
			button.addEventListener('click', async function () {
				var response = await fetch(window.ctenRuntimeLab.restUrl.replace(/\/$/, '') + '/runtime-lab/report', {
					credentials: 'same-origin',
					headers: { 'X-WP-Nonce': window.ctenRuntimeLab.restNonce }
				});
				var report = await response.json();
				var blob;
				if (button.getAttribute('data-format') === 'text') {
					blob = new Blob([stringifyReport(report)], { type: 'text/plain;charset=utf-8' });
				} else {
					blob = new Blob([JSON.stringify(report)], { type: 'application/json;charset=utf-8' });
				}
				var link = document.createElement('a');
				link.href = URL.createObjectURL(blob);
				link.download = 'cten-runtime-report.' + (button.getAttribute('data-format') === 'text' ? 'txt' : 'json');
				link.click();
				window.setTimeout(function () {
					URL.revokeObjectURL(link.href);
				}, 1000);
			});
		});

		var simulator = q('[data-cten-profile-simulator]');
		var simulateButton = q('[data-cten-profile-simulate]');
		if (simulator && simulateButton) {
			simulateButton.addEventListener('click', async function () {
				simulateButton.disabled = true;
				try {
					var data = await runtimeLabRequest('/runtime-lab/profile-simulate', {
						pageUrl: q('input[name="runtime_context[page_url]"]', simulator).value,
						pagePath: q('input[name="runtime_context[page_path]"]', simulator).value,
						postId: q('input[name="runtime_context[post_id]"]', simulator).value,
						postType: q('input[name="runtime_context[post_type]"]', simulator).value,
						category: q('input[name="runtime_context[category]"]', simulator).value,
						tag: q('input[name="runtime_context[tag]"]', simulator).value,
						referrer: q('input[name="runtime_context[referrer]"]', simulator).value,
						device: q('select[name="runtime_context[device]"]', simulator).value,
						loggedIn: q('select[name="runtime_context[logged_in]"]', simulator).value,
						userRole: q('input[name="runtime_context[user_role]"]', simulator).value,
						utmSource: q('input[name="runtime_context[utm_source]"]', simulator).value,
						utmMedium: q('input[name="runtime_context[utm_medium]"]', simulator).value,
						utmCampaign: q('input[name="runtime_context[utm_campaign]"]', simulator).value,
						utmContent: q('input[name="runtime_context[utm_content]"]', simulator).value,
						industry: q('input[name="runtime_context[industry]"]', simulator).value,
						campaign: q('input[name="runtime_context[campaign]"]', simulator).value
					});
					var output = q('[data-cten-profile-output]');
					if (output) {
						output.textContent = stringifyReport(data);
					}
				} catch (error) {
					console.error(error);
				} finally {
					simulateButton.disabled = false;
				}
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		copyOrigin();
		mirrorPreview();
		copyDiagnostics();
		warnUnsavedChanges();
		profileSearch();
		wireRuntimeLab();
	});
})();
