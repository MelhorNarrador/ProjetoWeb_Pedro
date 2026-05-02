# FloralQ – Plataforma de Monitorização de Plantas

**Universidade Europeia / IADE – Engenharia Informática**  
**Projeto de Desenvolvimento Web – 4º Semestre (2025/2026)**  
**Autor**: Pedro António  

**GitHub**: [Repositorio GitHub](https://github.com/MelhorNarrador/ProjetoWeb_Pedro)

---

<img src="Imgs/FloralQ_Logo.svg" alt="Logo FloralQ" width="2000">

---

## Palavras-chave 
IoT, sensores ambientais, monitorização de plantas, geolocalização, dashboard web, agricultura urbana, planeamento urbano

---

## 1. Introdução  
A FloralQ é uma plataforma web de monitorização de plantas que combina sensores IoT com uma interface web, permitindo aos utilizadores acompanhar o estado das suas plantas em tempo real.
Na primeira entrega, foram definidos o conceito, o público-alvo e a arquitetura geral do sistema. Foi também implementada a primeira versão da base de dados e iniciado o desenvolvimento do backend.
Nesta segunda entrega, o foco foi o desenvolvimento do protótipo funcional, incluindo a implementação completa da API, a integração com o hardware, o algoritmo preditivo de secagem e os mockups das interfaces finais.

---

## 2. Enquadramento e Problema  
O cuidado de plantas e espaços verdes é frequentemente baseado em observação manual e experiência empírica. Muitas pessoas têm dificuldade em perceber quando regar uma planta, o que pode levar a problemas como excesso ou falta de água. Além disso, a monitorização de plantas em jardins ou espaços urbanos não é normalmente acompanhada por dados objetivos.
A FloralQ pretende resolver este problema através de uma plataforma que combina sensores físicos de humidade do solo e localização GPS com uma aplicação web, permitindo monitorizar o estado das plantas e analisar dados ao longo do tempo.

---

## 3. Casos de uso  



---

## 4. Arquitetura e Tecnologias  
A arquitetura do sistema segue um modelo Cliente–Servidor em quatro camadas:

Dispositivo IoT: ESP32 com sensor de humidade do solo, módulo GPS e ecrã 1.69"
Backend: API REST em PHP responsável por receber, validar e processar dados
Base de Dados: PostgreSQL
Frontend Web: Interface desenvolvida em HTML, CSS e JavaScript
  
O dispositivo ESP32 comunica diretamente com o backend via HTTP, enviando leituras de humidade e coordenadas GPS de 5 em 5 minutos.  
O frontend consome a mesma API para apresentar os dados ao utilizador.  

---

## 5. User Flows e Wireframes

### 5.1 User Flow  
![UserFlow](Imgs/UserflowFloralQ.jpg)

O user flow ilustra a navegação completa da aplicação. Após o login, o utilizador chega ao dashboard onde pode:

Aceitar o tutorial e ser guiado pelo website
Adicionar uma nova planta preenchendo nome, localização, tipo, idade e associando um dispositivo
Interagir com os cards de planta visualizando o gráfico de humidade, abrindo os detalhes e consultando o mapa GPS (caso o dispositivo tenha GPS)
Editar ou remover uma planta a partir do modal de detalhes
O ecrã do dispositivo apresenta uma mascote (estilo Tamagochi) cujo estado reflete o estado da planta, utilizado em dispositivos FloralQ Home

### 5.2 User Stories / Storyboards  
  #### 5.2.1 Criar Conta / Criar Planta  
![Wireframe2](Imgs/Wireframe2.jpeg)  

Flow:  

1. O utilizador entra no website e clica em "Create Account"
2. Preenche o formulário de registo e faz login
3. Seleciona "Redeem Device" e insere o código de ativação, o dispositivo fica ligado à sua conta
4. Clica em "+ Add Plant" e preenche o formulário
5. A planta é criada e o card aparece no dashboard

  #### 5.2.2 Verificar Predição de Secagem / Verificar Localização da Planta  
  ![Wireframe1](Imgs/Wireframe1.jpeg)  
  
  Flow:  

1. O utilizador pode ver no card da planta o tempo estimado para secar
2. Se clicar, tem mais detalhes: hora/data prevista para secar, % de secagem por hora e confiança da predição
3. Para ver a localização, o utilizador clica no card da planta que quer consultar
4. Se o dispositivo for FloralQ Professional e existir pelo menos 1 leitura válida de GPS, o mapa aparece com pin no modal

---

## 6. Base de Dados
A base de dados foi implementada em PostgreSQL, gerida com pgAdmin4. 
Schema: - [Schema Base de Daos FloralQ](Sql/Create1.0.sql)
