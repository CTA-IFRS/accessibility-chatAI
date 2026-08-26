                    ┌──────────────────┐
                    │   ORCHESTRATOR   │
                    └────────┬─────────┘
                             │
             ┌───────────────┼────────────────┐
             │               │                │
             ▼               ▼                ▼
      backend-agent    frontend-agent   architecture-agent
             │               │                │
             ▼               ▼                ▼
      documentations/   documentations/  documentations/
        backend/          frontend/       architecture/
             │               │                │
             └───────────────┼────────────────┘
                             ▼
                  documentation-reviewer
                             │
                             ▼
                       FINAL DOCUMENTATION