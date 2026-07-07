# Installation

Open-source release note: this package includes third-party n8n assets with their original licenses.

1. Install dependencies locally when Node.js is available:
   - `npm install`
2. Build the frontend assets:
   - `npm run build`
3. Create the production ZIP by packaging the plugin folder contents, excluding `node_modules`, `.git`, and `dist` itself as an archive member.
4. Upload the ZIP in WordPress:
   - `Plugins` > `Add New` > `Upload Plugin`
5. Activate the plugin.
6. Open `Chat Trigger Embed` > `Connection` and paste the n8n production Chat Trigger URL.
7. Clear caches if needed:
   - WordPress cache plugin
   - LiteSpeed Cache
   - CDN or host-level cache

If the chatbot does not appear, verify that:

* The plugin is enabled
* A production webhook URL is saved
* The workflow is active
* Allowed Origins contains your WordPress origin
