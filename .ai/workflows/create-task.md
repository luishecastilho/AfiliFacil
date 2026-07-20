# Workflow: Create Task in Linear

Create a new issue in the **AfiliFacil** project on the **LHC Technology** team.

> **Board:** https://linear.app/lhc-technology/project/afilifacil-d01e3db6bbd3

## When to Use

- User asks to create a task, issue, or ticket
- You identify work that should be tracked (bug found during implementation, follow-up needed)
- Breaking a large feature into subtasks

## Prerequisites

- `LINEAR_API_KEY` must be set in root `.env` (already present) — **never hardcode it**, always `source .env`
- All tasks go to team **LHC Technology** and project **AfiliFacil** — **no exceptions.** This account
  works across many projects; sending a task to the wrong project is a critical error.

## Rules

- **Do NOT ask for confirmation before creating.** Infer all properties from the user's message and create immediately.
- If the user provides a title, that's enough — create the task right away.
- Only ask the user a question if truly ambiguous (e.g., can't tell if it's a bug or feature).
- **All task titles and descriptions must be written in English.**

## Procedure

### 1. Determine Task Properties

Infer from the user's message (do NOT ask unless ambiguous):

| Property     | Required | Default                    |
|-------------|----------|----------------------------|
| Title       | Yes      | —                          |
| Description | Yes      | —                          |
| Priority    | No       | 3 (Medium)                 |
| Label       | No       | Infer from task type        |
| State       | No       | Backlog                    |
| Assignee    | No       | Luis (auto)                |

**Priority mapping:** 1=Urgent, 2=High, 3=Medium, 4=Low, 0=None

**Label inference (type):**
- New capability → Feature (`8dba5674-3582-4175-8bb4-4008385b16a2`)
- Something broken → Bug (`bfe09914-61c8-4e64-b2a6-5adfa5cf73bb`)
- Enhancement → Improvement (`98489ec7-1ecb-465d-926c-75f21b265593`)
- Performance → Optimization (`aa6ab656-5fdb-467d-9e9a-5a79fa4e3b75`)
- Always add → Ai-generated (`53c222c4-267f-465e-85e7-4e9043705ade`)

**Label inference (scope — this is a Laravel + React app, add when clear):**
- Backend only (PHP, jobs, actions, DB) → BACKEND-ONLY (`61aa1c23-2092-400a-9cb0-21ac420818a5`)
- Frontend only (React/Inertia, UI) → FRONT-ONLY (`c06bcc50-3740-4d77-beef-3f4c5b8f5f32`)
- Touches both → BACK-FRONT (`eacc2426-8e5e-4524-90a5-489e3ea52948`)

### 2. Create the Issue

```bash
source .env && curl -s -X POST https://api.linear.app/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: $LINEAR_API_KEY" \
  -d '{
    "query": "mutation CreateIssue($input: IssueCreateInput!) { issueCreate(input: $input) { success issue { id identifier title url } } }",
    "variables": {
      "input": {
        "teamId": "0dce112f-6e73-4823-8e58-2f8174bc8759",
        "projectId": "08e76828-b9d8-4a9b-8432-41e9c378f5d8",
        "title": "TITLE_HERE",
        "description": "DESCRIPTION_HERE",
        "priority": 3,
        "stateId": "c8128876-01ec-44cb-a284-94bf640020a6",
        "labelIds": ["53c222c4-267f-465e-85e7-4e9043705ade"],
        "assigneeId": "8e617cc5-7430-4e12-b6c4-c013a09bec16"
      }
    }
  }' | python3 -m json.tool
```

### 3. Report Back

After creation, report:
- Issue identifier (e.g., `LHC-42`)
- Title
- URL (clickable link to Linear)

### 4. Update Local Backlog (Optional)

If the task is high priority or part of current work, add it to `.ai/backlog.md` under the appropriate section.

---

## Retrieving Tasks

### Get All Open Issues for AfiliFacil

```bash
source .env && curl -s -X POST https://api.linear.app/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: $LINEAR_API_KEY" \
  -d '{
    "query": "{ project(id: \"08e76828-b9d8-4a9b-8432-41e9c378f5d8\") { issues(filter: { state: { type: { nin: [\"completed\", \"canceled\"] } } }, orderBy: updatedAt, first: 50) { nodes { identifier title state { name } priority priorityLabel labels { nodes { name } } url } } } }"
  }' | python3 -m json.tool
```

### Get a Specific Issue by Number

Replace `NN` with the issue number (e.g. for `LHC-224`, use `224`). Note: the `id` returned here
is the issue UUID needed by the **Update Issue State** mutation below.

> The old `issueSearch(query: ...)` query is **deprecated** by Linear — use the project + number
> filter below instead.

```bash
source .env && curl -s -X POST https://api.linear.app/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: $LINEAR_API_KEY" \
  -d '{
    "query": "{ project(id: \"08e76828-b9d8-4a9b-8432-41e9c378f5d8\") { issues(filter: { number: { eq: NN } }, first: 1) { nodes { id identifier title description state { name } priority priorityLabel labels { nodes { name } } url } } } }"
  }' | python3 -m json.tool
```

### Update Issue State

```bash
source .env && curl -s -X POST https://api.linear.app/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: $LINEAR_API_KEY" \
  -d '{
    "query": "mutation { issueUpdate(id: \"ISSUE_UUID\", input: { stateId: \"STATE_UUID\" }) { success issue { identifier title state { name } } } }"
  }' | python3 -m json.tool
```

---

## Reference IDs

All IDs belong to the **LHC Technology** team. Only the **project ID** is AfiliFacil-specific.

| Entity                  | ID                                       |
|-------------------------|------------------------------------------|
| Team (LHC Technology)   | `0dce112f-6e73-4823-8e58-2f8174bc8759`  |
| Project (AfiliFacil)    | `08e76828-b9d8-4a9b-8432-41e9c378f5d8`  |
| Assignee (Luis)         | `8e617cc5-7430-4e12-b6c4-c013a09bec16`  |

### Workflow State IDs

| State       | ID                                       |
|-------------|------------------------------------------|
| Backlog     | `c8128876-01ec-44cb-a284-94bf640020a6`  |
| To do       | `3ab3e55f-f184-40d9-9cfe-b3310f6d1459`  |
| In Progress | `72fd89bd-c361-4d3c-93bb-d26bdff68869`  |
| In Review   | `a2e1b560-aa02-4a21-bca4-39e437b86ec3`  |
| Done        | `a9e1492a-0c3d-47f1-b0a9-d7d986c77519`  |
| Canceled    | `eb0f9352-622a-4585-87b3-88cc70392d41`  |

### Label IDs

| Label        | ID                                       |
|--------------|------------------------------------------|
| Feature      | `8dba5674-3582-4175-8bb4-4008385b16a2`  |
| Bug          | `bfe09914-61c8-4e64-b2a6-5adfa5cf73bb`  |
| Improvement  | `98489ec7-1ecb-465d-926c-75f21b265593`  |
| Optimization | `aa6ab656-5fdb-467d-9e9a-5a79fa4e3b75`  |
| Ai-generated | `53c222c4-267f-465e-85e7-4e9043705ade`  |
| BACKEND-ONLY | `61aa1c23-2092-400a-9cb0-21ac420818a5`  |
| FRONT-ONLY   | `c06bcc50-3740-4d77-beef-3f4c5b8f5f32`  |
| BACK-FRONT   | `eacc2426-8e5e-4524-90a5-489e3ea52948`  |

---

## Description Template

When creating tasks, use this structure for the description (Markdown):

```markdown
## Context
Why this work is needed.

## Scope
- [ ] What changes in the app

## Acceptance Criteria
- Specific, testable outcomes

## Notes
Any additional context, links, or references.
```
