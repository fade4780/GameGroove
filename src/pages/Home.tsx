import React from 'react';
import styled from 'styled-components';
import { motion } from 'framer-motion';
import { Link } from 'react-router-dom';
import { FaGamepad, FaUsers, FaChartLine, FaStar } from 'react-icons/fa';

const HeroSection = styled.section`
  text-align: center;
  padding: var(--spacing-xl) 0;
  background: var(--gradient-dark);
`;

const HeroTitle = styled.h1`
  font-size: 3rem;
  margin-bottom: var(--spacing-lg);
  color: var(--text-primary);
  
  span {
    color: var(--accent-primary);
  }
`;

const HeroSubtitle = styled.p`
  font-size: 1.2rem;
  color: var(--text-secondary);
  max-width: 600px;
  margin: 0 auto var(--spacing-xl);
`;

const CTAButton = styled(Link)`
  display: inline-block;
  padding: var(--spacing-md) var(--spacing-lg);
  background: var(--gradient-primary);
  color: var(--text-primary);
  text-decoration: none;
  border-radius: var(--radius-md);
  font-weight: bold;
  transition: var(--transition-fast);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }
`;

const FeaturesGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--spacing-lg);
  padding: var(--spacing-xl) 0;
`;

const FeatureCard = styled(motion.div)`
  background: var(--bg-secondary);
  padding: var(--spacing-lg);
  border-radius: var(--radius-md);
  text-align: center;
  border: 1px solid var(--accent-primary);
  
  h3 {
    color: var(--accent-primary);
    margin: var(--spacing-md) 0;
  }
  
  p {
    color: var(--text-secondary);
  }
`;

const FeaturedProjects = styled.section`
  padding: var(--spacing-xl) 0;
`;

const ProjectGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-lg);
  margin-top: var(--spacing-lg);
`;

const ProjectCard = styled(motion.div)`
  background: var(--bg-secondary);
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 1px solid var(--accent-primary);
  
  img {
    width: 100%;
    height: 200px;
    object-fit: cover;
  }
`;

const ProjectInfo = styled.div`
  padding: var(--spacing-md);
  
  h3 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
  }
  
  p {
    color: var(--text-secondary);
    font-size: 0.9rem;
  }
`;

function Home() {
  return (
    <>
      <HeroSection>
        <HeroTitle>
          Welcome to <span>GameGroove</span>
        </HeroTitle>
        <HeroSubtitle>
          Where indie game developers and passionate gamers come together to create amazing experiences.
        </HeroSubtitle>
        <CTAButton to="/projects">Explore Projects</CTAButton>
      </HeroSection>

      <FeaturesGrid>
        <FeatureCard
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.2 }}
        >
          <FaGamepad size={40} color="var(--accent-primary)" />
          <h3>Playable Demos</h3>
          <p>Experience games before they're complete and help shape their development.</p>
        </FeatureCard>

        <FeatureCard
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.4 }}
        >
          <FaUsers size={40} color="var(--accent-primary)" />
          <h3>Community Driven</h3>
          <p>Your feedback matters. Vote on features and influence game development.</p>
        </FeatureCard>

        <FeatureCard
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.6 }}
        >
          <FaChartLine size={40} color="var(--accent-primary)" />
          <h3>Transparent Funding</h3>
          <p>See exactly how your support helps bring games to life.</p>
        </FeatureCard>
      </FeaturesGrid>

      <FeaturedProjects>
        <h2>Featured Projects</h2>
        <ProjectGrid>
          {/* Example project cards - these would be populated from your API */}
          <ProjectCard
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 0.2 }}
          >
            <img src="/placeholder-game1.jpg" alt="Game 1" />
            <ProjectInfo>
              <h3>Pixel Adventure</h3>
              <p>75% funded • 14 days left</p>
            </ProjectInfo>
          </ProjectCard>

          <ProjectCard
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 0.4 }}
          >
            <img src="/placeholder-game2.jpg" alt="Game 2" />
            <ProjectInfo>
              <h3>Space Explorer</h3>
              <p>120% funded • 5 days left</p>
            </ProjectInfo>
          </ProjectCard>

          <ProjectCard
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 0.6 }}
          >
            <img src="/placeholder-game3.jpg" alt="Game 3" />
            <ProjectInfo>
              <h3>Mystery Quest</h3>
              <p>45% funded • 21 days left</p>
            </ProjectInfo>
          </ProjectCard>
        </ProjectGrid>
      </FeaturedProjects>
    </>
  );
}

export default Home; 