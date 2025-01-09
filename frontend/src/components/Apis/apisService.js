import axios from "axios";

export async function checkConnection() {
  try {
    const response = await axios.get(
      "http://localhost:8181/config/conexao.php",
      {
        responseType: "text",
      }
    );
    console.log("Conexão bem-sucedida:", response);
    return response.data; // Retorna a mensagem recebida
  } catch (error) {
    console.error("Erro ao conectar ao backend:", error);
    throw new Error("Erro ao conectar ao backend.");
  }
}
