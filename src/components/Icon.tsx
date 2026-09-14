/**
 * Icon — maps Material Symbols ligature names → Lucide React icons.
 * Drop-in replacement for <span className="material-symbols-outlined">name</span>
 *
 * Usage:
 *   <Icon name="arrow_forward" size={18} className="text-amber-500" />
 */
import React from 'react';
import {
  Home, Info, Mail, Phone, MapPin, Globe, Link, ExternalLink,
  ChevronRight, ChevronLeft, ChevronDown, ChevronUp,
  ArrowRight, ArrowLeft, ArrowUp, ArrowUpRight,
  X, Plus, Minus, Check, CheckCircle, XCircle, HelpCircle, AlertCircle,
  Search, Filter, Settings, Settings2, Sliders,
  Edit, Edit2, Edit3, Trash2, Save, Copy, Download, Upload,
  Eye, EyeOff, Lock, Unlock, LogOut, LogIn,
  User, Users, UserPlus, UserCheck,
  Bell, BellRing, Star, StarOff, Heart, Flag,
  Image, Camera, FileText, File, Folder, FolderOpen, Archive,
  Send, Share2, MessageSquare, MessageCircle,
  Building, Building2, Landmark, GraduationCap, BookOpen, Lightbulb,
  Award, Medal, Shield, ShieldCheck, Verified,
  Activity, Heart as HeartPulse, Stethoscope,
  Briefcase, Target, TrendingUp, BarChart2,
  Globe2, Network, Layers, Code, Terminal, Cpu,
  RefreshCw, RotateCcw, ZoomIn, Maximize2, Minimize2,
  Menu, Grid, List, LayoutGrid,
  Calendar, Clock, History,
  DollarSign, Banknote, Receipt,
  Phone as PhoneIcon, Video, Mic,
  Newspaper, Rss, Tag, Bookmark,
  Sparkles, Wand2, Bot, Brain,
  MapPin as Pin, Navigation, Compass,
  Package, Box, ShoppingBag,
  Sun, Moon, Zap, Flame, Leaf,
  PlayCircle, PauseCircle, StopCircle,
  AlignLeft, AlignCenter, Bold, Italic,
  Hash, AtSign, Percent,
  Database, Server, Cloud, CloudUpload, CloudDownload,
  Webhook, GitBranch, Code2,
  Handshake, HandHeart,
  ScrollText, ClipboardList, ClipboardCheck,
  Building as AdminIcon, ToggleLeft, ToggleRight,
} from 'lucide-react';

// Map every Material Symbol name used in this project to a Lucide component
const ICON_MAP: Record<string, React.ComponentType<{ size?: number; className?: string; strokeWidth?: number }>> = {
  // Navigation & Arrows
  home: Home,
  home_pin: Pin,
  arrow_forward: ArrowRight,
  arrow_back: ArrowLeft,
  arrow_upward: ArrowUp,
  chevron_right: ChevronRight,
  chevron_left: ChevronLeft,
  expand_more: ChevronDown,
  expand_less: ChevronUp,
  open_in_new: ExternalLink,
  link: Link,

  // Actions
  add: Plus,
  close: X,
  delete: Trash2,
  edit: Edit2,
  edit_document: Edit3,
  save: Save,
  send: Send,
  search: Search,
  search_off: Filter,
  copy: Copy,
  content_copy: Copy,
  download: Download,
  upload_file: Upload,
  cloud_upload: CloudUpload,
  add_photo_alternate: Image,
  post_add: FileText,
  sync: RefreshCw,
  refresh: RefreshCw,
  zoom_in: ZoomIn,
  visibility: Eye,
  visibility_off: EyeOff,
  share: Share2,
  download_done: CheckCircle,

  // Status & Feedback
  check_circle: CheckCircle,
  task_alt: CheckCircle,
  cancel: XCircle,
  info: Info,
  help: HelpCircle,
  help_outline: HelpCircle,
  verified: ShieldCheck,
  verified_user: ShieldCheck,
  error: AlertCircle,
  warning: AlertCircle,
  stars: Star,
  bolt: Zap,
  auto_fix_high: Sparkles,
  lightbulb: Lightbulb,

  // Auth & Security
  lock: Lock,
  lock_open: Unlock,
  logout: LogOut,
  login: LogIn,
  admin_panel_settings: Shield,

  // People
  person: User,
  person_add: UserPlus,
  person_celebrate: UserCheck,
  group: Users,
  groups: Users,
  badge: Award,
  handshake: Handshake,
  volunteer_activism: Heart,

  // Communication
  mail: Mail,
  alternate_email: AtSign,
  contact_mail: Mail,
  phone: PhoneIcon,
  language: Globe,
  chat: MessageCircle,
  forum: MessageSquare,

  // Content & Media
  newspaper: Newspaper,
  campaign: Rss,
  notifications_active: BellRing,
  notifications: Bell,
  photo_library: LayoutGrid,
  photo_camera: Camera,
  image: Image,
  folder_open: FolderOpen,
  folder_zip: Archive,
  list_alt: ClipboardList,
  format_quote: Italic,
  history_edu: ScrollText,
  policy: ScrollText,
  clinical_notes: ClipboardCheck,

  // Education & Foundation
  school: GraduationCap,
  menu_book: BookOpen,
  account_balance: Landmark,
  account_tree: Network,
  workspace_premium: Medal,
  psychology: Brain,
  calculate: BarChart2,

  // Health
  health_and_safety: HeartPulse,
  favorite: Heart,

  // Building & Location
  location_on: MapPin,
  apartment: Building2,
  hub: Network,
  layers: Layers,

  // Tech & Dev
  terminal: Terminal,
  php: Code2,
  code: Code,
  web: Globe2,
  dns: Server,
  storage: Database,

  // UI Controls
  menu: Menu,
  tune: Sliders,
  dashboard: LayoutGrid,
  apps: Grid,
  toggle_on: ToggleRight,
  toggle_off: ToggleLeft,

  // Money & Impact
  volunteer: HandHeart,
  impact: TrendingUp,
  flag: Flag,

  // Misc
  progress_activity: RefreshCw,
  done: Check,
};

interface IconProps {
  name: string;
  size?: number;
  className?: string;
  strokeWidth?: number;
}

export function Icon({ name, size = 20, className = '', strokeWidth = 1.75 }: IconProps) {
  const LucideIcon = ICON_MAP[name.trim().toLowerCase()];

  if (!LucideIcon) {
    // Unknown icon — render nothing (no raw text flash)
    return null;
  }

  return <LucideIcon size={size} className={className} strokeWidth={strokeWidth} />;
}

export default Icon;
