{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "0.5.0";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-RutLVUZ1YbypX0O1WwBJ731mKMvTW/qmws58qx0U44M=";
}
