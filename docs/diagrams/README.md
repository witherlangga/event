Diagrams for SAD

Files:
- erd.puml : entity relationship diagram for database schema
- transaction_flow.puml / .mmd : sequence diagram for purchase/payment/confirm
- deployment.puml / .mmd : deployment diagram
- runbook_flow.puml / .mmd : runbook activity diagram for expired orders cleanup

How to render:
- PlantUML (.puml): Use PlantUML or online PlantUML server to render PNG/SVG.
- Mermaid (.mmd): Use mermaid-cli or online mermaid live editor to render.

Example:
- plantuml erd.puml
- plantuml transaction_flow.puml
- mmdc -i transaction_flow.mmd -o transaction_flow.svg
