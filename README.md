# Afterlife

## Instruções

### Clonando o projeto

1. Crie uma pasta
2. Abra esta pasta no terminal
3. Digite no terminal: `git clone https://github.com/projAfterLife/proj_after_life.git`
4. Abra a pasta chamada **afterlife** no VS Code
5. Clique em **main** no canto inferior esquerdo da tela e **troque para a sua branch**

### Instruções de merge com a branch beta

- Verifique se a sua branch está atualizada com a branch beta
- Envie as suas atualizações para a sua branch antes de fazer um pull request para a beta
- Verifique se a sua branch e a beta não possuem conflitos na hora de realizar um pull request
- **Não faça um push diretamente para a beta**

## Ferramentas

# DOCKER

0. Baixe o [DOCKER](https://docs.docker.com/desktop/install/windows-install/) caso não tenha ainda

1. Com o terminal na pasta proj_after_life, digite:
   `docker compose up -d`

2. verifique se os 5 containers estão ativos (mysql_db, phpmyadmin, php_fpm, frontend_vue e nginx)

3. digite `npm i` na **raiz** do projeto (onde tem as pastas backend e frontend) e digite **novamente** dentro da pasta **frontend**

- O projeto está dividido em 3 links:

front-end: `http://localhost:5173` <br>
back-end: `http://localhost` <br>
phpmyadmin: `http://localhost:8080`

- Caso precise deletar os containers e criá-los novamente, digite: <br>
   `docker compose down -v`<br>
   `docker compose build`<br>
   `docker compose up -d`
 