require('dotenv').config();
const express = require('express');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

app.get('/health', (req, res) => {
  res.json({ status: 'ok', message: 'Sistema de Donaciones API running' });
});

// Routes (se agregarán en sprints posteriores)
// app.use('/api/auth', require('./routes/auth'));
// app.use('/api/donations', require('./routes/donations'));
// app.use('/api/campaigns', require('./routes/campaigns'));

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});

module.exports = app;
