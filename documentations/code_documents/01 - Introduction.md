Version: V1.1.0

# Introduction

## Purpose

The purpose of the document is to outline the technical process flow of IMIS and the coding conventions that have been followed. The main target of this documentation is to provide insight on how IMIS has been developed in terms of coding, conventions followed and work flow of the system. This document shall act as a reference to any developers working on IMIS about the work flow and technicalities of the code.

## Target Audience

Target Audiences of this manual are the core system developers of IMIS.

## Core Cross-Cutting References

The following documents describe behavior shared across multiple IMIS modules:

- `02 - Technical Information.md`: application structure, sessions, frontend conventions, layouts, and shared cache requirements.
- `03 - Dashboard.md`: shell-first dashboard loading, content endpoints, main-dashboard caching, invalidation, navigation loading, and dashboard testing.
- `15 - API documentation.md`: API routes, request formats, responses, and API login throttling.
- `22 - Authentication and Web Security.md`: browser and API login controls, trusted proxies, password-reset privacy, clickjacking protection, deployment, and security tests.
