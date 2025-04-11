import React, { useState } from 'react';
import { useParams } from 'react-router-dom';
import styled from 'styled-components';
import { motion } from 'framer-motion';
import { FaHeart, FaComment, FaShare, FaPlay } from 'react-icons/fa';

const ProjectContainer = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  padding: var(--spacing-xl) var(--spacing-md);
`;

const ProjectHeader = styled.div`
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: var(--spacing-lg);
  margin-bottom: var(--spacing-xl);
  
  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const DemoSection = styled.div`
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--accent-primary);
`;

const DemoHeader = styled.div`
  padding: var(--spacing-md);
  background: var(--gradient-dark);
  display: flex;
  justify-content: space-between;
  align-items: center;
`;

const DemoButton = styled.button`
  background: var(--gradient-primary);
  color: var(--text-primary);
  border: none;
  padding: var(--spacing-sm) var(--spacing-md);
  border-radius: var(--radius-sm);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-weight: bold;
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }
`;

const DemoFrame = styled.div`
  width: 100%;
  height: 400px;
  background: var(--bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
`;

const InfoSection = styled.div`
  background: var(--bg-secondary);
  padding: var(--spacing-lg);
  border-radius: var(--radius-lg);
  border: 1px solid var(--accent-primary);
`;

const ProgressBar = styled.div`
  height: 10px;
  background: var(--bg-primary);
  border-radius: var(--radius-sm);
  margin: var(--spacing-md) 0;
  overflow: hidden;
`;

const ProgressFill = styled.div<{ progress: number }>`
  height: 100%;
  background: var(--gradient-primary);
  width: ${props => props.progress}%;
  transition: width 0.3s ease;
`;

const FundingInfo = styled.div`
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--spacing-md);
  margin: var(--spacing-lg) 0;
`;

const FundingStat = styled.div`
  text-align: center;
  
  h3 {
    color: var(--accent-primary);
    margin-bottom: var(--spacing-sm);
  }
  
  p {
    color: var(--text-secondary);
  }
`;

const ActionButtons = styled.div`
  display: flex;
  gap: var(--spacing-md);
  margin-top: var(--spacing-lg);
`;

const ActionButton = styled.button`
  flex: 1;
  padding: var(--spacing-md);
  border: 1px solid var(--accent-primary);
  background: transparent;
  color: var(--text-primary);
  border-radius: var(--radius-sm);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  transition: var(--transition-fast);
  
  &:hover {
    background: rgba(108, 92, 231, 0.1);
  }
`;

const CommunitySection = styled.section`
  margin-top: var(--spacing-xl);
`;

const TabButtons = styled.div`
  display: flex;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
`;

const TabButton = styled.button<{ active: boolean }>`
  padding: var(--spacing-md) var(--spacing-lg);
  background: ${props => props.active ? 'var(--accent-primary)' : 'transparent'};
  color: var(--text-primary);
  border: 1px solid var(--accent-primary);
  border-radius: var(--radius-sm);
  cursor: pointer;
  
  &:hover {
    background: ${props => props.active ? 'var(--accent-primary)' : 'rgba(108, 92, 231, 0.1)'};
  }
`;

function ProjectDetail() {
  const { id } = useParams();
  const [activeTab, setActiveTab] = useState('updates');
  const [isDemoPlaying, setIsDemoPlaying] = useState(false);

  // Mock data - would come from your API
  const project = {
    title: "Pixel Adventure",
    description: "A retro-style platformer with modern mechanics",
    funding: {
      current: 75000,
      goal: 100000,
      backers: 1500,
      daysLeft: 14
    }
  };

  const progress = (project.funding.current / project.funding.goal) * 100;

  return (
    <ProjectContainer>
      <ProjectHeader>
        <DemoSection>
          <DemoHeader>
            <h2>{project.title}</h2>
            <DemoButton onClick={() => setIsDemoPlaying(!isDemoPlaying)}>
              <FaPlay />
              {isDemoPlaying ? 'Stop Demo' : 'Play Demo'}
            </DemoButton>
          </DemoHeader>
          <DemoFrame>
            {isDemoPlaying ? (
              <iframe
                src="/game-demo"
                width="100%"
                height="100%"
                frameBorder="0"
                allow="fullscreen"
              />
            ) : (
              <img src="/placeholder-game1.jpg" alt="Game Preview" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            )}
          </DemoFrame>
        </DemoSection>

        <InfoSection>
          <h2>Funding Progress</h2>
          <ProgressBar>
            <ProgressFill progress={progress} />
          </ProgressBar>
          
          <FundingInfo>
            <FundingStat>
              <h3>${project.funding.current.toLocaleString()}</h3>
              <p>pledged of ${project.funding.goal.toLocaleString()} goal</p>
            </FundingStat>
            <FundingStat>
              <h3>{project.funding.backers}</h3>
              <p>backers</p>
            </FundingStat>
            <FundingStat>
              <h3>{project.funding.daysLeft}</h3>
              <p>days to go</p>
            </FundingStat>
          </FundingInfo>

          <ActionButtons>
            <ActionButton>
              <FaHeart />
              Back Project
            </ActionButton>
            <ActionButton>
              <FaShare />
              Share
            </ActionButton>
          </ActionButtons>
        </InfoSection>
      </ProjectHeader>

      <CommunitySection>
        <TabButtons>
          <TabButton
            active={activeTab === 'updates'}
            onClick={() => setActiveTab('updates')}
          >
            Updates
          </TabButton>
          <TabButton
            active={activeTab === 'comments'}
            onClick={() => setActiveTab('comments')}
          >
            Comments
          </TabButton>
          <TabButton
            active={activeTab === 'community'}
            onClick={() => setActiveTab('community')}
          >
            Community
          </TabButton>
        </TabButtons>

        {/* Tab content would go here */}
      </CommunitySection>
    </ProjectContainer>
  );
}

export default ProjectDetail; 