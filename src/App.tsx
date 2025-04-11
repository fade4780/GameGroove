import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import styled from 'styled-components';
import './styles/theme.css';

// Components
import Navbar from './components/Navbar';
import Footer from './components/Footer';

// Pages
import Home from './pages/Home';
import Projects from './pages/Projects';
import ProjectDetail from './pages/ProjectDetail';
import StudioDashboard from './pages/StudioDashboard';
import UserProfile from './pages/UserProfile';
import Transparency from './pages/Transparency';

const AppContainer = styled.div`
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--gradient-dark);
`;

const MainContent = styled.main`
  flex: 1;
  padding: var(--spacing-xl) 0;
`;

function App() {
  return (
    <Router>
      <AppContainer>
        <Navbar />
        <MainContent>
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/projects" element={<Projects />} />
            <Route path="/projects/:id" element={<ProjectDetail />} />
            <Route path="/studio" element={<StudioDashboard />} />
            <Route path="/profile" element={<UserProfile />} />
            <Route path="/transparency" element={<Transparency />} />
          </Routes>
        </MainContent>
        <Footer />
      </AppContainer>
    </Router>
  );
}

export default App; 