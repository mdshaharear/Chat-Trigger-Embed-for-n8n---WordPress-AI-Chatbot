# Chatbot Profiles

Version 1.4.0 introduces a structured profile model. A profile is a complete chatbot configuration with a name, description, enabled state, production Chat Trigger URL, identity text, initial messages, quick actions, theme preset, launcher label, metadata controls, visibility rules, campaign rules, and priority.

Profiles are stored inside the main `cten_settings` option as a structured array. This avoids creating one WordPress option per small field and keeps import/export and migration work manageable.

Only one profile renders on a page by default. Profiles are sorted by priority, and exclusion rules override inclusion rules. Developers can filter the resolved profile with `cten_resolved_profile`.

Example profiles created during migration include:

* Main Website Assistant
* Restaurant Demo
* Dentist Demo
* Real Estate Demo
* Pricing Assistant
* Support Assistant

Demo profiles are disabled by default and require a production n8n Chat Trigger URL before use.
