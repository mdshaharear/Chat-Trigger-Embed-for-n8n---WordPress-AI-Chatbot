# Rollback

If you need to revert to the previous internal release:

1. Back up the current WordPress database.
2. Export the plugin settings from the Tools page.
3. Deactivate the current plugin build.
4. Upload `dist/chat-trigger-embed-for-n8n-1.1.0-internal.zip`.
5. Reactivate the plugin.
6. Restore the exported settings if needed.
7. Clear all website caches.
8. Verify the chatbot loads and responds.

Keep the rollback ZIP alongside the final release ZIP for local recovery.
