{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "1.1.0";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-OrfWdfAg2Zk4pveXFkIqCUZcadkjg+Et9f6Q9qZEcVA=";
}
