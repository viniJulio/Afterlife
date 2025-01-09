//Pra adicionar outra página:
//Importa a view
//Adiciona na const routes
//Pronto

import { createRouter, createWebHistory } from "vue-router";

import Home from "@/views/Home.vue";
import Admin from "@/views/Admin.vue";
import Usuarios from "@/views/Usuarios.vue";
import Texts from "@/views/Texts.vue";
import Videos from "@/views/Videos.vue";
import Audios from "@/views/Audios.vue";
import Documentos from "@/views/Documentos.vue";
import Imagens from "@/views/Imagens.vue";
import Folders from "@/views/Folders.vue";
import Senhas from "@/views/Senhas.vue";
import AcessoAdmin from "@/views/AcessoAdmin.vue";
import RedefinirSenha from "@/views/RedefinirSenha.vue";

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
  },
  {
    path: "/admin",
    name: "Admin",
    component: Admin,
  },
  {
    path: "/usuarios",
    name: "Usuarios",
    component: Usuarios,
  },
  {
    path: "/pastas",
    name: "Folders",
    component: Folders,
  },
  {
    path: "/texto/:id",
    name: "Texts",
    component: Texts,
  },
  {
    path: "/video/:id",
    name: "Videos",
    component: Videos,
  },
  {
    path: "/audio/:id",
    name: "Audios",
    component: Audios,
  },
  {
    path: "/documento/:id",
    name: "Documentos",
    component: Documentos,
  },
  {
    path: "/imagem/:id",
    name: "Imagens",
    component: Imagens,
  },
  {
    path: "/senha/:id",
    name: "Senhas",
    component: Senhas,
  },
  {
    path: "/acessoAdmin",
    name: "viewAcessoAdmin",
    component: AcessoAdmin,
  },
  {
    path: "/redefinirSenha",
    name: "viewRedefinirSenha",
    component: RedefinirSenha,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
