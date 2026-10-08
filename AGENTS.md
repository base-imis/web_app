# Project Agent Instructions

## Jira ticket automation

When the user asks to add, create, or file a Jira ticket for this workspace, treat that request as authorization to create the issue immediately. Do not stop after drafting the ticket and do not ask for a second confirmation unless required ticket content is genuinely ambiguous.

Use these defaults:

- Jira base URL: `https://jira.innovativesolution.com.np/`
- Board: `Task Tracking-BaseIMIS`
- Username: `Priyankas`
- Password source: `JIRA_PASSWORD` environment variable or an authenticated Jira connector/session. Never store, print, commit, or repeat the password.

Creation workflow:

1. Resolve the board with `GET /rest/agile/1.0/board?name=Task%20Tracking-BaseIMIS`.
2. Read its configuration and filter to determine the project key instead of guessing.
3. Read project metadata to determine the supported issue type and allowed fields.
4. Create the issue with `POST /rest/api/2/issue` using the smallest valid payload.
5. If Jira rejects optional fields, remove the rejected fields and retry.
6. Report the resulting issue key and link.

Unless the user requests a shorter ticket, include:

- problem/background and expected behavior;
- implementation scope;
- relevant routes, APIs, files, or modules discovered in the workspace;
- QA acceptance criteria and test scenarios;
- assignee or priority only when requested or clearly supplied.

Do not disclose Jira credentials in ticket content, command output, documentation, commits, or chat responses.
