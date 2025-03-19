{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "1.1.1";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-ZsEdqc1CFAoCoo0+uydNuO7SczzeqRR+s2wrlI11qeI=";
}
