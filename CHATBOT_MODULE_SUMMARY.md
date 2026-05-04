# Chatbot Module Summary

The chatbot feature adds a stored assistant conversation layer on top of the existing JobNest conversations and messages infrastructure.

What changed:

- Added a dedicated chatbot API surface under `/api/chatbot/*`.
- Reused the existing `conversations`, `conversation_participants`, and `messages` tables instead of creating a separate chat subsystem.
- Extended conversations with a `chatbot` type so each authenticated user can have one stored assistant thread.
- Extended messages with a `message_role` field so user messages and assistant replies can live in the same timeline.
- Added a dedicated `ChatbotService` to call the external AI endpoint from Laravel only.

How it works:

1. Flutter calls Laravel.
2. Laravel creates or reuses the authenticated user’s chatbot conversation.
3. Laravel stores the user message in the existing messages table.
4. Laravel sends the recent message history plus system prompt and user context to the external AI endpoint.
5. Laravel stores the assistant reply as another persisted message.
6. Laravel returns the reply payload to Flutter.

History handling:

- The service sends only a recent history window, controlled by `CHATBOT_HISTORY_LIMIT`.
- The assistant reply is persisted and becomes part of the same conversation timeline.

Assumptions:

- The external AI endpoint already exists and accepts a JSON request with recent message history.
- The chatbot is a support assistant for JobNest, not a general autonomous agent.
- The chatbot conversation is owner-scoped to the authenticated user and is not visible to unrelated users.
