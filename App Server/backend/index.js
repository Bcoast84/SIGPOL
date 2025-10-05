const express = require('express');
const sql = require('mssql');
const cors = require('cors');
const app = express();
app.use(express.json());
app.use(cors());

// Configuración de SQL Server
const config = {
  user: 'tu_usuario_sql',
  password: 'tu_contraseña_sql',
  server: 'IP_VM_SQL', // Ejemplo: '192.168.100.10'
  database: 'SIGPOL',
  options: {
    encrypt: false,
    trustServerCertificate: true
  }
};

// Endpoint de prueba
app.get('/personas', async (req, res) => {
  try {
    await sql.connect(config);
    const result = await sql.query('SELECT * FROM Persona');
    res.json(result.recordset);
  } catch (err) {
    res.status(500).send(err.message);
  }
});

app.listen(3000, () => console.log('SIGPOL backend corriendo en puerto 3000'));
