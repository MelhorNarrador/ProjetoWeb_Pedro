# FloralQ - Plataforma de Monitorização de Plantas

**Universidade Europeia / IADE**
**Licenciatura em Engenharia Informática - Projeto de Desenvolvimento Web - 4.º Semestre (2025/2026)**  
**Autor:** Pedro António - N.º 20241273  
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
- **Fase 1 - Pesquisa pre-inicial** (1 semana1): definição do conceito, análise de mercado, público-alvo, e pre planeamento.
- **Fase 2 - Conceitualização** (3 semanas): definição da arquitetura, desenvolvimento da base de dados(1.0), documentação e proposta da idea.
- **Fase 3 - Implementação** (7 semanas): implementação do backend, testes de API, algoritmo predictivo, implementação de Iot, mockups, design system, documentação intermedia e protótipo funcional.
- **Fase 4 - Polimento** (3 semanas): implementação da UI final, backoffice, testes de usabilidade, refatoração de código e documentação final.
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
| **Planta App** | App móvel | Interface intuitiva  Recomendações personalizadas   Identificação de espécies por imagem | Não utiliza sensores físicos   Baseia-se exclusivamente em inputs manuais do utilizador   Sem dados objetivos | Integração de sensores reais com dados automáticos, sem depender de input manual |
| **Weenat** | Plataforma agro | Monitorização com sensores reais   Dados ambientais detalhados (temperatura, humidade, chuva) | Foco exclusivo na agricultura profissional   sistema complexo e caro   interface pouco acessível ao utilizador comum | Solução mais simples, acessível e orientada ao utilizador doméstico e urbano |
| **Greg App** | App móvel | Lembretes de rega baseados em espécie e localização | Sem sensores   Baseado em calendário fixo, não em dados reais do solo | Dados reais do sensor substituem suposições baseadas em calendário |

### 4.2 Posicionamento da FloralQ

A FloralQ diferencia-se por ser a única solução que combina, num produto acessível ao utilizador comum:

1. **Sensores físicos de humidade** com leituras automáticas a cada 5 minutos.
2. **Algoritmo preditivo** que estima quando a planta vai precisar de água (não apenas alerta quando já está seca).
3. **Geolocalização GPS** para plantas em jardins ou espaços urbanos.
4. **Interface web responsiva** com dashboard visual, sem necessidade de app móvel (Possivel implementação futura).
5. **Mascote animada** no ecrã do dispositivo, que torna a interação intuitiva mesmo sem aceder ao dashboard.

---

## 5. Público-Alvo e User Research

### 5.1 Segmentos de Utilizadores

A FloralQ dirige-se a três perfis distintos, com necessidades e contextos de utilização diferentes.

**Utilizadores Domésticos**
- **Perfil demográfico:** todas as idades, estudantes ou profissionais, residentes em apartamentos ou moradias com plantas.
- **Hábitos e comportamentos:** Compram plantas por estética e bem-estar, mas têm pouco conhecimento técnico sobre cuidados. Utilizam smartphone diariamente e estão confortáveis com apps web.
- **Necessidade principal:** Saber quando regar sem ter de verificar manualmente todos os dias.
- **Frustração atual:** Plantas que morrem por excesso ou falta de água, sem perceber porquê.

**Jardins Privados e Hortas Domésticas**
- **Perfil demográfico:** todas as idades, proprietários de jardins ou pequenas hortas.
- **Hábitos e comportamentos:** Gerem múltiplas plantas de espécies diferentes, com necessidades de rega distintas. Preferem soluções práticas a aplicações complexas. Valorizam dados históricos para perceber padrões sazonais.
- **Necessidade principal:** Gestão centralizada de múltiplas plantas com alertas.
- **Frustração atual:** Impossibilidade de monitorizar todas as plantas ao mesmo tempo, especialmente em jardins maiores.

**estores de Espaços Verdes Urbanos**
- **Perfil demográfico:** 30-50 anos, técnicos de jardinagem ou gestores municipais responsáveis por parques e jardins urbanos.
- **Hábitos e comportamentos:** Gerem dezenas de plantas. Precisam de relatórios e dados estatísticos para justificar decisões de manutenção. Trabalham com sistemas de informação geográfica.
- **Necessidade principal:** Visualização da localização e estado de todas as plantas num mapa.
- **Frustração atual:** Ausência de ferramentas acessíveis que integrem IoT com SIG.

### 5.2 Personas e User Journey

As personas e a user journey foram desenvolvidas com base numa investigação UI/UX que foi feita através de entrevistas a 3 participantes selecionados por conveniência(representantes do público alvo). As entrevistas recolheram dados sobre hábitos, motivações, frustrações e expectativas.  

[L2: Ver UX Case Study completo](https://github.com/MelhorNarrador/ProjetoWeb_Pedro/blob/1770febdf1bf600f499eba23e7a905e824d92ba5/Documentos/UI_Assets_%26_Design_System/FloralQ-User-Research.pdf)

---

## 6. Casos de Uso e Modelo de Domínio

### 6.1 Atores do Sistema

O sistema da FloralQ tem cinco atores principais:

- **Utilizador Anónimo:** Pode aceder à página de login, registar-se e criar uma conta.
- **Utilizador Registado:** Pode gerir plantas, associar dispositivos, consultar o dashboard, configurar alertas, alterar palavra-passe e customizar modo de apresentação.
- **Utilizador Registado com Dispositivo Profissional:** Tem acesso adicional à localização GPS da planta no mapa.
- **Administrador:** Gere utilizadores, dispositivos e plantas através do backoffice; tem acesso ao dashboard estatístico.
- **Sistema:** É responsável por emitir alertas automáticos (in-app e email) quando os limiares de humidade são ultrapassados.
- **Dispositivo IoT (ESP32):** Ator externo que envia leituras de humidade e GPS via API REST, sem autenticação de sessão.  

<img src="Imgs/Use_Case_FloralQ_Updated.jpg" alt="Use Case Diagram" width="500">

### 6.2 Modelo de Domínio  
<img src="Imgs/Modelo_de_Domínio_FloralQ.jpg" alt="Use Case Diagram" width="1000">  

O modelo de domínio representa cinco entidades centrais e as suas relações:

- **User_Account:** Representa um utilizador registado. Tem um papel (user/admin), email, password hash, configurações de alerta e preferências de dashboard.
- **Device:** Representa um sensor ESP32. Pertence a um User_Account (após resgate) e pode ser do tipo Home ou Professional (com GPS). Tem um código de ativação único usado no registo inicial(Depois de uso -> NULL).
- **Plant:** Representa uma planta monitorizada. Pertence a um User_Account, está associada a um Device e tem um Plant_Type. Pode ter uma imagem.
- **Plant_Type:** Catálogo de espécies com limiares de humidade mínimo e máximo.
- **Sensor_Reading:** Cada leitura enviada pelo sensor. Contém percentagem de humidade, coordenadas GPS (opcionais) e timestamp. Está associada ao Device que a gerou.

---

## 7. Arquitetura e Tecnologias

### 7.1 Arquitetura do Sistema

A arquitetura da FloralQ segue um modelo **Cliente-Servidor em quatro camadas**, com comunicação HTTP entre todas as camadas:

**Dispositivo IoT**
- ESP32 + Sensor de humidade + Módulo GPS (Professional) + Ecrã LCD (Home)
- Comunicação com o backend via HTTP POST (JSON) a cada 5 minutos
  
**Backend**
- PHP + Apache (XAMPP, necessario para SSE(Workers))
- API REST com endpoints de autenticação, CRUD, IoT e administração
- Sessões PHP
- Cookie protegido com `HttpOnly = True`, `SameSite = Strict`, `Lifetime = 0`
- PDO com prepared statements em todos os endpoints para prevenção de SQL Injection
- Operações destrutivas usam transações PDO para garantir integridade de dados
- Endpoints privados protegidos por `RequireAuth` e `RequireAdmin` para endpoints de administrador
- Server-Sent Events (SSE) para atualizações em tempo real no dashboard
- Password com Hash Bcrypt  

**Base de Dados**
- PostgreSQL com 5 tabelas
- Acesso via PDO com prepared statements para prevenção de SQL Injection
- Índice `idx_sensor_device_time` para otimização de queries temporais

**Frontend Web**
- HTML + CSS + JavaScript, sem frameworks
- Gráficos: Chart.js (doughnut + linha)
- Mapas: Google Maps iframe
- Onboarding: Driver.js
- Autenticação via sessão PHP server-side

### 7.2 Padrões e Decisões de Arquitetura

- **API REST sem estado (stateless exceto para sessão PHP):** Cada endpoint tem responsabilidade única, retorna sempre JSON.
- **Separação de endpoints IoT e web:** Os endpoints IoT (`insert_reading.php`, `register_device.php`, `get_plant_state.php`) não requerem sessão PHP e validam apenas pelo `device_code` do dispositivo. Os endpoints web requerem sessão via `requireAuth()`.
- **Server-Sent Events (SSE):** O dashboard usa `sse_plants.php` e o backoffice usa `sse_admin.php`, ambos verificando periodicamente alterações na base de dados e emitindo eventos sem necessidade de polling do frontend.
- **Prepared statements PDO:** Em todos os acessos à base de dados, prevenindo SQL Injection.
- **Bcrypt:** Para hash de passwords, com `password_hash()` / `password_verify()`.
- **JS modular:** No frontend, com separação de responsabilidades: `apiClient.js`, `chartController.js`, `plantCard.js`, `modalManager.js`, `onboarding.js`, `toast.js`.

### 7.3 Stack Tecnológica

| Camada | Tecnologia | Justificação |
|---|---|---|
| Backend | PHP | Requisito do briefing   XAMPP necessário para suporte a SSE com Workers |
| Base de Dados | PostgreSQL | Suporte a `SERIAL`, `TIMESTAMPTZ`, `DECIMAL`, `ON CONFLICT DO UPDATE`   Melhor para dados temporais |
| Frontend | HTML + CSS + JavaScript | Sem framework, Requisito do briefing |
| Gráficos | Chart.js | Suporta doughnut e line |
| Mapas | Google Maps iframe | Integração via iframe com coordenadas GPS   Não requer API key |
| Onboarding | Driver.js | Tour interativo passo a passo |
| IoT | ESP32 (Arduino IDE) | Microcontrolador com WiFi integrado |
| Sensor | Sensor de humidade | Necessario para medir humidade do solo |
| GPS | Módulo GPS, TinyGPSPlus | Necessario para obter localização GPS |
| Ecrã | LCD 1.69", TFT_eSPI | Display colorido para a mascote animada |
| Ferramentas | VSCode, pgAdmin4, Postman, GitHub, Figma | Ferramentas standard de desenvolvimento |

---
