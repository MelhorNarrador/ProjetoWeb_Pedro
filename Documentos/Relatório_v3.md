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
12. [Hardware IoT - ESP32](#12-hardware-iot--esp32)
13. [UI/UX - Design System e Interfaces](#13-uiux--design-system-e-interfaces)
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

---

## 2. Enquadramento e Problema

### 2.1 Contexto

O cuidado de plantas e espaços verdes é frequentemente baseado em **observação manual** e **experiência empírica**. A ausência de dados objetivos sobre o estado do solo leva a dois problemas recorrentes: o excesso de rega, que apodrece as raízes, e a falta de rega, que resseca a planta. Nenhum dos dois é imediatamente visível a olho nu antes de o dano estar feito.

Paralelamente, o crescimento dos **espaços verdes urbanos** e a popularização das plantas domésticas, criou uma nova geração de utilizadores que valoriza a manutenção de plantas mas não dispõe de experiência acumulada ou de ferramentas tecnológicas acessíveis para o fazer corretamente.

### 2.2 Problema

O problema central que a FloralQ procura resolver é a **falta de informação objetiva e em tempo real** sobre o estado das plantas. Mais especificamente:

- Os utilizadores domésticos não sabem **quando regar** nem **quanto regar**.
- Os gestores de espaços verdes urbanos e privados não têm uma plataforma acessível que centralize dados de múltiplas plantas e forneça alertas automáticos.
- As soluções existentes são ou demasiado complexas e caras (destinadas à agricultura profissional) ou demasiado simplistas (sem sensores físicos ou baseadas apenas em inputs manuais).  

### 2.3 Solução Proposta

A **FloralQ** resolve este problema através de uma plataforma que combina **sensores físicos IoT** (humidade do solo + GPS) com uma **aplicação web**, permitindo:

- Monitorizar o estado das plantas em tempo real, com dados objetivos.
- Prever automaticamente quando a planta vai precisar de água, através de um algoritmo de regressão linear.
- Geolocalizar as plantas em jardins ou espaços urbanos.
- Gerir múltiplas plantas por utilizador, com histórico de leituras e alertas configuráveis.
- Apresentar uma mascote animada no ecrã do dispositivo IoT que reflete visualmente o estado da planta (estilo Tamagotchi).

---

## 3. Objetivos e Motivação

### 3.1 Objetivos

- Desenvolver uma aplicação web completa para monitorização de plantas, com backend PHP, base de dados PostgreSQL e frontend HTML/CSS/JS.
- Integrar sensores IoT (ESP32 com sensor de humidade, módulo GPS e ecrã LCD) com o backend via API REST.
- Implementar um algoritmo preditivo de secagem baseado em regressão linear com filtragem de outliers para integridade de dados.
- Apresentar dados num dashboard visual com gráficos de humidade e mapas de localização.
- Produzir um dashboard estatístico dinâmico a partir dos dados da base de dados.
- Garantir uma interface intuitiva, validada por testes de usabilidade com utilizadores reais.
- Integrar um sistema de informação geográfica para localização das plantas.

### 3.2 Motivação

Mesmo com o crescimento dos espaços verdes urbanos e o interesse generalizado por plantas domésticas, continua a ser difícil encontrar uma plataforma que disponibilize ferramentas tecnológicas acessíveis para a manutenção correta de plantas. A FloralQ pretende preencher esse espaço, tornando a monitorização mais **precisa, automatizada e acessível**, combinando hardware simples com uma interface web intuitiva.

---

## 4. Pesquisa de Mercado e Análise Competitiva

Para fundamentar o posicionamento da FloralQ, foram analisadas as principais soluções existentes no mercado, avaliando os seus pontos fortes, pontos fracos e a oportunidade que cada um deixa em aberto.

### 4.1 Tabela Comparativa

| Plataforma | Tipo | Pontos Fortes | Pontos Fracos | Oportunidade para FloralQ |
|---|---|---|---|---|
| **Planta App** | App móvel | Interface intuitiva  recomendações personalizadas   identificação de espécies por imagem | Não utiliza sensores físicos; baseia-se exclusivamente em inputs manuais do utilizador; sem dados objetivos | Integração de sensores reais com dados automáticos, sem depender de input manual |
| **Weenat** | Plataforma agro | Monitorização com sensores reais; dados ambientais detalhados (temperatura, humidade, chuva) | Foco exclusivo na agricultura profissional; sistema complexo e caro; interface pouco acessível ao utilizador comum | Solução mais simples, acessível e orientada ao utilizador doméstico e urbano |
| **Greg App** | App móvel | Lembretes de rega baseados em espécie e localização | Sem sensores; baseado em calendário fixo, não em dados reais do solo | Dados reais do sensor substituem suposições baseadas em calendário |
