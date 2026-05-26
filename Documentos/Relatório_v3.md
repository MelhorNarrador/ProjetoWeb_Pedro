# FloralQ – Plataforma de Monitorização de Plantas

**Universidade Europeia / IADE**
**Licenciatura em Engenharia Informática – Projeto de Desenvolvimento Web – 4.º Semestre (2025/2026)**  
**Autor:** Pedro António – N.º 20241273  
**GitHub**: [L1: Repositorio GitHub](https://github.com/MelhorNarrador/ProjetoWeb_Pedro)

<img src="Imgs/FloralQ_Logo.svg" alt="Logo FloralQ" width="2000">

---

## Palavras-chave

IoT, sensores ambientais, monitorização de plantas, geolocalização, dashboard web, agricultura urbana, regressão linear, algoritmo preditivo, PHP, PostgreSQL, ESP32

---

## Índice

1. [Introdução](#1-introdução)
2. [Enquadramento e Problema](#2-enquadramento-e-problema)
3. [Objetivos e Motivação](#3-objetivos-e-motivação)
4. [Pesquisa de Mercado e Análise Competitiva](#4-pesquisa-de-mercado-e-análise-competitiva)
5. [Público-Alvo e User Research](#5-público-alvo-e-user-research)
6. [Casos de Uso e Modelo de Domínio](#6-casos-de-uso-e-modelo-de-domínio)
7. [Arquitetura e Tecnologias](#7-arquitetura-e-tecnologias)
8. [Base de Dados](#8-base-de-dados)
9. [Documentação da API REST](#9-documentação-da-api-rest)
10. [Algoritmo Preditivo de Secagem](#10-algoritmo-preditivo-de-secagem)
11. [Sistema de Informação Geográfica](#11-sistema-de-informação-geográfica)
12. [Hardware IoT – ESP32](#12-hardware-iot--esp32)
13. [UI/UX – Design System e Interfaces](#13-uiux--design-system-e-interfaces)
14. [User Flow e User Stories](#14-user-flow-e-user-stories)
15. [Backoffice de Administração](#15-backoffice-de-administração)
16. [Dashboard Estatístico](#16-dashboard-estatístico)
17. [Testes de Usabilidade](#17-testes-de-usabilidade)
18. [Planeamento e Execução](#18-planeamento-e-execução)
19. [Contribuição por Unidade Curricular](#19-contribuição-por-unidade-curricular)
20. [Conclusão](#20-conclusão)

---

## 1. Introdução

Este relatório documenta o desenvolvimento completo da **FloralQ**, uma plataforma web de monitorização de plantas desenvolvida no âmbito da unidade curricular de Projeto de Desenvolvimento Web do 4.º semestre da Licenciatura em Engenharia Informática da Universidade Europeia / IADE.

O projeto foi desenvolvido segundo a metodologia **Project-Based Learning (PBL)**, integrando as cinco unidades curriculares do semestre: Projeto de Desenvolvimento Web, Programação Web, Interfaces e Usabilidade, Estatística e Sistemas de Informação Geográficos, Algoritmos e Estruturas de Dados.

O desenvolvimento decorreu ao longo de **14 semanas**, organizado em quatro fases:
- **Fase 1 – Pesquisa pre-inicial** (1 semana1): definição do conceito, análise de mercado, público-alvo, e pre planeamento.
- **Fase 2 – Conceitualização** (3 semanas): definição da arquitetura, desenvolvimento da base de dados(1.0), documentação e proposta da idea.
- **Fase 3 – Implementação** (7 semanas): implementação do backend, testes de API, algoritmo predictivo, implementação de Iot, mockups, design system, documentação intermedia e protótipo funcional.
- **Fase 4 – Polimento** (3 semanas): implementação da UI final, backoffice, testes de usabilidade, refatoração de código e documentação final.
Este documento agrega e atualiza os conteúdos das entregas anteriores, incorporando as melhorias sugeridas pela docente, e apresenta o estado final da solução  
<img src="Imgs/Gantt 25_05.jpg" alt="Gantt 25/05" width="2000">







