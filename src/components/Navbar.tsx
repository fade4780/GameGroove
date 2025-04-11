import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import styled from 'styled-components';
import { motion } from 'framer-motion';
import { FaGamepad, FaUser, FaSearch } from 'react-icons/fa';

const Nav = styled.nav`
  background: var(--bg-secondary);
  padding: var(--spacing-md);
  border-bottom: 2px solid var(--accent-primary);
  box-shadow: var(--shadow-lg);
`;

const NavContainer = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
`;

const Logo = styled(Link)`
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  text-decoration: none;
  color: var(--text-primary);
  font-size: 1.5rem;
  font-weight: bold;
  
  &:hover {
    color: var(--accent-primary);
  }
`;

const NavLinks = styled.div`
  display: flex;
  gap: var(--spacing-lg);
  align-items: center;
`;

const NavLink = styled(Link)`
  color: var(--text-secondary);
  text-decoration: none;
  padding: var(--spacing-sm) var(--spacing-md);
  border-radius: var(--radius-sm);
  transition: var(--transition-fast);
  
  &:hover {
    color: var(--text-primary);
    background: rgba(108, 92, 231, 0.1);
  }
  
  &.active {
    color: var(--accent-primary);
    border-bottom: 2px solid var(--accent-primary);
  }
`;

const SearchBar = styled.div`
  display: flex;
  align-items: center;
  background: var(--bg-primary);
  padding: var(--spacing-sm);
  border-radius: var(--radius-sm);
  border: 1px solid var(--accent-primary);
`;

const SearchInput = styled.input`
  background: transparent;
  border: none;
  color: var(--text-primary);
  padding: var(--spacing-sm);
  width: 200px;
  
  &:focus {
    outline: none;
  }
`;

const UserMenu = styled.div`
  position: relative;
`;

const UserButton = styled.button`
  background: transparent;
  border: none;
  color: var(--text-primary);
  cursor: pointer;
  padding: var(--spacing-sm);
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  
  &:hover {
    color: var(--accent-primary);
  }
`;

const Dropdown = styled(motion.div)`
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--bg-secondary);
  border: 1px solid var(--accent-primary);
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
  min-width: 200px;
  box-shadow: var(--shadow-lg);
`;

const DropdownItem = styled(Link)`
  display: block;
  padding: var(--spacing-sm) var(--spacing-md);
  color: var(--text-secondary);
  text-decoration: none;
  transition: var(--transition-fast);
  
  &:hover {
    color: var(--text-primary);
    background: rgba(108, 92, 231, 0.1);
  }
`;

function Navbar() {
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const navigate = useNavigate();

  return (
    <Nav>
      <NavContainer>
        <Logo to="/">
          <FaGamepad />
          GameGroove
        </Logo>
        
        <NavLinks>
          <NavLink to="/projects">Projects</NavLink>
          <NavLink to="/transparency">Transparency</NavLink>
          
          <SearchBar>
            <FaSearch />
            <SearchInput placeholder="Search games..." />
          </SearchBar>
          
          <UserMenu>
            <UserButton onClick={() => setIsDropdownOpen(!isDropdownOpen)}>
              <FaUser />
              Profile
            </UserButton>
            
            {isDropdownOpen && (
              <Dropdown
                initial={{ opacity: 0, y: -10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
              >
                <DropdownItem to="/profile">My Profile</DropdownItem>
                <DropdownItem to="/studio">Studio Dashboard</DropdownItem>
                <DropdownItem to="/settings">Settings</DropdownItem>
                <DropdownItem to="/logout">Logout</DropdownItem>
              </Dropdown>
            )}
          </UserMenu>
        </NavLinks>
      </NavContainer>
    </Nav>
  );
}

export default Navbar; 